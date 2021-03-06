<?php
/**
 * Created by PhpStorm.
 * User: BTH
 * Date: 06.01.16
 * Time: 14:40
 *
 * Запуск из крона каждый час
 *
 */
namespace app\commands;

use app\models\Actypes;
use app\models\BillingUserBalance;
use app\models\Fleet;
use app\models\Pax;
use app\models\Schedule;
use yii\console\Controller;

class PaxController extends Controller
{
    public function actionIndex()
    {
        $this->hoursUp();
        //$this->generatePaxes();
    }

    public function actionByDay(){
        $this->generatePaxes(Schedule::nextAll());
    }

    private function hoursUp()
    {
        foreach (Pax::find()->all() as $pax)
        {
            $pax->waiting_hours+=1;
            $pax->updated = gmdate('Y-m-d H:i:s');
            $pax->save();
            if($pax->waiting_hours>72) {
                //Списать вуки со счета компании
                $ub = BillingUserBalance::find()->andWhere(['user_vid'=>0])->one();
                if(!$ub) {
                    $ub = new BillingUserBalance();
                    $ub->user_vid = 0;
                }
                $ub->balance-=$pax->num_pax*2;
                $ub->save();
                $pax->delete();
            }
        }
    }

    private function generatePaxes($schedule = false)
    {
        if($schedule == false){
            $schedule = Schedule::inHour();
        }

        foreach($schedule as $paxdata)
        {
            if (!$pax = Pax::find()->andWhere('from_icao="' . $paxdata->dep . '"')->andWhere('to_icao="' . $paxdata->arr . '"')->andWhere('waiting_hours=0')->one()) {
                $pax = new Pax();
                $pax->created = gmdate('Y-m-d H:i:s');
            }

            $pax->from_icao = $paxdata->dep;
            $pax->to_icao = $paxdata->arr;
            $pax->waiting_hours = 0;
            $pax->num_pax += (int)$this->generateRandomPaxes($paxdata->aircraft);

            $pax->save();
        }
    }
    private function generateRandomPaxes($acftype)
    {
        $acf = Fleet::randByType($acftype);
        $coff = rand(50, 100) / 100;

        return round(($acf ? ($acf->max_pax > 0 ? $acf->max_pax : 100) : 101) * $coff);
    }
}