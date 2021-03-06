<?php namespace App\Http\Controllers;

use App\Helper;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Prize;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PrizeController extends Controller {

    /**
     * Redirect to result page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index($date = null)
    {
        $lang = config('app.locale');
        return redirect()->route('result_' . $lang, $date);
    }

    /**
     * Display all lottery result in our database.
     *
     * @return View
     */
    public function list_all(Prize $prize)
    {
        $prizes = $prize->get();
        return view('index', compact('prizes'));
    }

    /**
     * Display a result of the specific date.
     *
     * @return View
     */
    public function result(Prize $prize, $date = null)
    {
        $prizes = self::_get_result_from_date($date);
        $prizes = self::_sort_result($prizes);
        $dates  = self::_list_dates($prize);
        return view('result', compact('prizes', 'dates'));
    }

    /**
     * Display a result of the specific date in JSON.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function result_json($date = null)
    {
        $prizes = self::_get_result_from_date($date);
        return response()->json($prizes);
    }

    private function _sort_result(Array &$prizes)
    {
        $output = [];
        $output['date'] = $prizes['date'];
        $labels = [
            'first',
            'last_two_digits',
            'last_three_digits',
            'first_three_digits',
            'second',
            'third',
            'fourth',
            'fifth',
        ];

        foreach ($labels as $label) {
            if (! array_key_exists($label, $prizes['prizes'])) {
                continue;
            }

            $output['prizes'][$label] = $prizes['prizes'][$label];
        }

        return $output;
    }

    /**
     * Return date list in JSON format
     *
     * @param Prize $prize
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list_dates(Prize $prize, $limit=49)
    {
        $dates = self::_list_dates($prize, $limit);
        return response()->json($dates);
    }

    private function _list_dates(Prize $prize, $limit=49)
    {
        $dates = $prize->select('date')->groupBy('date')->orderBy('date', 'desc')->get();
        $output = [];

        foreach ($dates as $date) {
            $output[] = [ 'numeric' => $date->date, 'human' => Helper::human_date($date->date) ];
        }

        return array_splice($output, 0, $limit);
    }

    /**
     * @param $date
     * @return mixed
     */
    private function _get_result_from_date($date)
    {
        if ($date) {
            $prizes = Prize::where('date', $date)->orderBy('prize')->get();
        }
        else {
            $subquery = 'date = (SELECT DISTINCT date FROM prizes ORDER BY date DESC LIMIT 1)';
            $prizes = Prize::whereRaw($subquery)->orderBy('prize')->get();
        }

        if ($prizes->isEmpty()) {
            abort(404);
        }

        return self::_prepare_result($prizes);
    }

    /**
     * @param $prizes
     * @return mixed
     */
    private function _prepare_result($prizes)
    {
        $date = $prizes->first()->date;

        $result['date'] = [
            'numeric' => $date,
            'human'   => Helper::human_date($date)
        ];

        foreach($prizes as $record) {
            $result['prizes'][$record->type][] = $record->prize;
        }

        return $result;
    }
}
