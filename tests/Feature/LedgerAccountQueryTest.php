<?php /** @noinspection PhpParamsInspection */

namespace Abivia\Ledger\Tests\Feature;

use Abivia\Ledger\Models\LedgerAccount;
use Abivia\Ledger\Tests\TestCaseWithMigrations;
use Abivia\Ledger\Tests\ValidatesJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test Ledger API calls that don't involve journal transactions.
 */
class LedgerAccountQueryTest extends TestCaseWithMigrations
{
    use CommonChecks;
    use CreateLedgerTrait;
    use RefreshDatabase;
    use ValidatesJson;

    public function setUp(): void
    {
        parent::setUp();
        LedgerAccount::resetRules();
        self::$expectContent = 'accounts';
    }

    public function testGet()
    {
        // First we need a ledger
        $this->createLedger(['template'], ['template' => 'manufacturer_1.0']);

        // Query for everything, paginated
        $pages = 0;
        $totalAccounts = 0;
        $requestData = [
            'limit' => 20,
        ];
        while (1) {
            $response = $this->json(
                'post', 'api/ledger/account/query', $requestData
            );
            $actual = $this->isSuccessful($response);
            // Check the response against our schema
            $this->validateResponse($actual, 'accountquery-response');
            $accounts = $actual->accounts;
            ++$pages;
            $totalAccounts += count($accounts);
            if (count($accounts) !== 20) {
                break;
            }
            $requestData['after'] = ['code' => end($accounts)->code];
        }
        $this->assertEquals(7, $pages);
        $this->assertEquals(139, $totalAccounts);
        //print_r($accounts[0]);
    }

    public function testGetNoLedger()
    {
        // Query for everything, paginated
        $requestData = [
            'limit' => 20,
        ];
        $response = $this->json(
            'post', 'api/ledger/account/query', $requestData
        );
        $actual = $this->isFailure($response);
    }

}
