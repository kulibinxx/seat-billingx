<?php

namespace Denngarr\Seat\Billing\Helpers;

use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Industry\CharacterMining;
use Seat\Eveapi\Models\Wallet\CorporationWalletJournal;
use Seat\Services\Models\UserSetting;
use Seat\Services\Repositories\Corporation\Members;
use Seat\Web\Models\User;

trait BillingHelper
{
    use Members;

    public function getCharacterBilling($corporation_id, $year, $month)
    {

        if (setting("pricevalue", true) == "m") {
            $ledger = DB::table('character_minings')->select('user_settings.value', DB::raw('SUM((character_minings.quantity / 100) * (invTypeMaterials.quantity * ' . 
                (setting("refinerate", true) / 100) . ') * market_prices.adjusted_price) as amounts'))
                ->join('invTypeMaterials', 'character_minings.type_id', 'invTypeMaterials.typeID')
                ->join('market_prices', 'invTypeMaterials.materialTypeID', 'market_prices.type_id')
                ->join('corporation_member_trackings', function($join) use ($corporation_id) {
                     $join->on('corporation_member_trackings.character_id', 'character_minings.character_id')
                         ->where('corporation_member_trackings.corporation_id', $corporation_id);
                })
                ->join('users', 'users.id', 'corporation_member_trackings.character_id')
                ->join('user_settings', function ($join) {
                    $join->on('user_settings.group_id', 'users.group_id')
                        ->where('user_settings.name', 'main_character_id');
                })
                ->where('year', $year)
                ->where('month', $month)
                ->groupby('user_settings.value')
                ->get();
        } else {

            $ledger = DB::table('character_minings')->select('user_settings.value', DB::raw('SUM(character_minings.quantity * market_prices.average_price) as amounts'))
                ->join('market_prices', 'character_minings.type_id', 'market_prices.type_id')
                ->join('corporation_member_trackings', function($join) use ($corporation_id) {
                     $join->on('corporation_member_trackings.character_id', 'character_minings.character_id')
                         ->where('corporation_member_trackings.corporation_id', $corporation_id);
                })
                ->join('users', 'users.id', 'corporation_member_trackings.character_id')
                ->join('user_settings', function ($join) {
                    $join->on('user_settings.group_id', 'users.group_id')
                        ->where('user_settings.name', 'main_character_id');
                })
                ->where('year', $year)
                ->where('month', $month)
                ->groupby('user_settings.value')
                ->get();
        }

        return $ledger;
    }

    private function getTrackingMembers($corporation_id)
    {
        return $this->getCorporationMemberTracking($corporation_id);
    }

    public function getMainsBilling($corporation_id, $year = null, $month = null)
    {
        if (is_null($year)) {
           $year = date('Y');
        }
        if (is_null($month)) {
           $month = date('n');
        }
     
        $summary = [];
        $taxrates = $this->getCorporateTaxRate($corporation_id);

        $ledger = $this->getCharacterBilling($corporation_id, $year, $month);

        foreach ($ledger as $entry) {
            if (!isset($summary[$entry->value])) {
                $summary[$entry->value]['amount'] = 0;
            }

            $summary[$entry->value]['amount'] += $entry->amounts;
            $summary[$entry->value]['id'] = $entry->value;
            $summary[$entry->value]['taxrate'] = $taxrates['taxrate'] / 100;
            $summary[$entry->value]['modifier'] = $taxrates['modifier'] / 100;
        }
        return $summary;
    }

    public function getCorporateTaxRate($corporation_id)
    {
        $reg_chars = 0;
        $tracking = $this->getTrackingMembers($corporation_id);
        $total_chars = $tracking->count();
        if ($total_chars == 0) {
            $total_chars = 1;
        }

        $reg_chars = $tracking->get()->filter(function ($value) {
            if (is_null($value->user))
                return false;

            return ! is_null($value->user->refresh_token);
        })->count();

        $mining_taxrate = setting('ioretaxrate', true);
        $mining_modifier = setting('ioremodifier', true);
        $pve_taxrate = setting('ibountytaxrate', true);

        if (($reg_chars / $total_chars) < (setting('irate', true) / 100)) {
            $mining_taxrate = setting('oretaxrate', true);
            $mining_modifier = setting('oremodifier', true);
            $pve_taxrate = setting('bountytaxrate', true);
        }

        return ['taxrate' => $mining_taxrate, 'modifier' => $mining_modifier, 'pve' => $pve_taxrate];
    }

    private function getMiningTotal($corporation_id, $year, $month)
    {
        $ledgers = $this->getCharacterBilling($corporation_id, $year, $month);

        return $ledgers->sum('amounts');
    }


    private function getBountyTotal($corporation_id, $year, $month)
    {
        $bounties = $this->getCorporationLedgerBountyPrizeByMonth($corporation_id, $year, $month);

        return $bounties->sum('total');
    }

    private function getCorporationBillingMonths($corporation_id)
    {
        if (!is_array($corporation_id)) {
            array_push($corporation_ids, $corporation_id);
        } else {
            $corporation_ids = $corporation_id;
        }

        return CorporationBill::select(DB::raw('DISTINCT month, year'))
            ->wherein('corporation_id', $corporation_ids)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    private function getCorporationBillByMonth($year, $month)
    {
        return CorporationBill::with('corporation')
            ->where("month", $month)
            ->where("year", $year)
            ->get();
    }

    private function getPastMainsBillingByMonth($corporation_id, $year, $month)
    {
        return CharacterBill::where("corporation_id", $corporation_id)
            ->where("month", $month)
            ->where("year", $year)
            ->get();
    }

}
