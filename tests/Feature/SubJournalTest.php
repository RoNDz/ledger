<?php
/** @noinspection PhpParamsInspection */

namespace Abivia\Ledger\Tests\Feature;

use Abivia\Ledger\Models\LedgerAccount;
use Abivia\Ledger\Tests\TestCaseWithMigrations;
use Abivia\Ledger\Tests\ValidatesJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Ledger Domain API calls.
 */
class SubJournalTest extends TestCaseWithMigrations
{
    use CommonChecks;
    use CreateLedgerTrait;
    use RefreshDatabase;
    use ValidatesJson;

    public array $baseRequest = [
        'code' => 'SJ',
        'names' => [
            [
                'name' => 'Sales Journal',
                'language' => 'en'
            ]
        ]
    ];


    public function setUp(): void
    {
        parent::setUp();
        LedgerAccount::resetRules();
        self::$expectContent = 'journal';
    }

    public function testBadRequest()
    {
        $response = $this->postJson(
            'api/ledger/journal/add', ['nonsense' => true]
        );
        $actual = $this->isFailure($response);
        // Check the response against our schema
        $this->validateResponse($actual, 'journal-response');
    }

    public function testAdd()
    {
        //Create a ledger
        $this->createLedger();

        // Add a sub-journal
        $response = $this->json(
            'post', 'api/ledger/journal/add', $this->baseRequest
        );
        $actual = $this->isSuccessful($response);
        // Check the response against our schema
        $this->validateResponse($actual, 'journal-response');
        $this->hasRevisionElements($actual->journal);
        $this->hasAttributes(['code', 'names'], $actual->journal);
        $this->assertEquals('SJ', $actual->journal->code);
        $this->assertCount(1, $actual->journal->names);
        $this->assertEquals('Sales Journal', $actual->journal->names[0]->name);
    }

    public function testAddDuplicate()
    {
        // First we need a ledger
        $this->createLedger();

        // Add SJ
        $this->json(
            'post', 'api/ledger/journal/add', $this->baseRequest
        );
        // Add SJ again
        $response = $this->json(
            'post', 'api/ledger/journal/add', $this->baseRequest
        );
        $this->isFailure($response);
    }

    public function testAddNoLedger()
    {
        // Add a sub-journal
        $response = $this->json(
            'post', 'api/ledger/journal/add', $this->baseRequest
        );
        $actual = $this->isFailure($response);
    }

    public function testDelete()
    {
        // First we need a ledger
        $this->createLedger();

        // Add a sub-journal
        $response = $this->json(
            'post', 'api/ledger/journal/add', $this->baseRequest
        );
        $addResult = $this->isSuccessful($response);

        // Now delete it
        $requestData = [
            'code' => 'SJ',
            'revision' => $addResult->journal->revision,
        ];
        $response = $this->json(
            'post', 'api/ledger/journal/delete', $requestData
        );
        $actual = $this->isSuccessful($response, 'success');
        // Check the response against our schema
        $this->validateResponse($actual, 'journal-response');

        // Confirm that a fetch fails
        $response = $this->json(
            'post', 'api/ledger/journal/get', $requestData
        );
        $this->isFailure($response);
    }

    public function testGet()
    {
        // First we need a ledger
        $this->createLedger();

        // Add a sub-journal
        $this->json(
            'post', 'api/ledger/journal/add', $this->baseRequest
        );

        // Now fetch the sub-journal again
        $requestData = [
            'code' => 'SJ',
        ];
        $response = $this->json(
            'post', 'api/ledger/journal/get', $requestData
        );
        $actual = $this->isSuccessful($response);
        $this->hasAttributes(
            ['code', 'names'],
            $actual->journal
        );
        $this->hasRevisionElements($actual->journal);
        $this->assertEquals('SJ', $actual->journal->code);

        // Expect error with invalid code
        $requestData = ['code' => 'bob'];
        $response = $this->json(
            'post', 'api/ledger/journal/get', $requestData
        );
        $this->isFailure($response);
    }

    /**
     * TODO: create a separate suite for updates where transactions present.
     */
    public function testUpdate()
    {
        // First we need a ledger
        $this->createLedger();

        // Add a sub-journal
        $this->json(
            'post', 'api/ledger/journal/add', $this->baseRequest
        );

        // Try an update with bogus data
        $requestData = [
            'revision' => 'bogus',
            'code' => 'SJ',
        ];
        $response = $this->json(
            'post', 'api/ledger/journal/update', $requestData
        );
        $this->isFailure($response);

        // Do a get so we have a valid revision
        $response = $this->json(
            'post', 'api/ledger/journal/get', $requestData
        );
        $actual = $this->isSuccessful($response);

        // Now try with a valid revision
        $requestData = [
            'revision' => $actual->journal->revision,
            'code' => 'SJ',
            'toCode' => 'EJ'
        ];
        $response = $this->json(
            'post', 'api/ledger/journal/update', $requestData
        );
        $result = $this->isSuccessful($response);
        $this->assertEquals('EJ', $result->journal->code);

        // Attempt a retry with the same (now invalid) revision.
        $requestData['code'] = 'EJ';
        $response = $this->json(
            'post', 'api/ledger/journal/update', $requestData
        );
        $this->isFailure($response);

    }

}
