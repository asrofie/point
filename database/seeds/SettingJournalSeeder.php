<?php

use App\Model\Accounting\ChartOfAccount;
use App\Model\SettingJournal;
use Illuminate\Database\Seeder;

class SettingJournalSeeder extends Seeder
{
    public $chartOfAccounts;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->chartOfAccounts = ChartOfAccount::all();

        $this->openingBalanceInventory();
        $this->purchase();
    }

    private function openingBalanceInventory()
    {
        $accounts = [
            'retained earning' => $this->getAccountId('modal disetor')
        ];

        foreach ($accounts as $key => $value) {
            $settingJournal = new SettingJournal;
            $settingJournal->feature = 'opening balance inventory';
            $settingJournal->name = $key;
            $settingJournal->description = '';
            $settingJournal->chart_of_account_id = $value;
            $settingJournal->save();
        }
    }

    private function purchase()
    {
        $accounts = [
            'account payable' => $this->getAccountId('utang usaha'),
            'down payment' => $this->getAccountId('uang muka pembelian'),
            'discount' => $this->getAccountId('potongan pembelian'),
            'income tax receivable' => $this->getAccountId('ppn masukan'),
        ];

        foreach ($accounts as $key => $value) {
            $settingJournal = new SettingJournal;
            $settingJournal->feature = 'purchase';
            $settingJournal->name = $key;
            $settingJournal->description = '';
            $settingJournal->chart_of_account_id = $value;
            $settingJournal->save();
        }
    }

    private function getAccountId($account)
    {
        foreach ($this->chartOfAccounts as $chartOfAccount) {
            info($chartOfAccount);
            if ($chartOfAccount->name == $account) {
                return $chartOfAccount->id;
            }
        }

        return null;
    }
}
