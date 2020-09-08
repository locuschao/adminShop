<?php
namespace library;

class Tool
{
    /**
     * @return array
     */
    public static function getDates()
    {
        return [
            'all'           => '全部',
            'today'         => '今天',
            'yesterday'     => '昨天',
            'current_week'  => '本周',
            'last_week'     => '上周',
            'current_month' => '本月',
            'last_month'    => '上月'
        ];
    }

    /**
     * 根据日期，获取对应的时间戳
     * @return array
     */
    public static function getDateBetween()
    {
        return [
            'all'           => [],
            'today'         => ['today 00:00:00', 'tomorrow 00:00:00'],
            'yesterday'     => ['yesterday 00:00:00', 'today 00:00:00'],
            'current_week'  => ['this week 00:00:00', 'next week 00:00:00'],
            'last_week'     => ['last week 00:00:00', 'this week 00:00:00'],
            'current_month' => ['first Day of this month 00:00:00', 'first Day of next month 00:00:00'],
            'last_month'    => ['first Day of last month 00:00:00', 'first Day of this month 00:00:00']
        ];
    }

    /**
     * 获取时间范围
     * @param $date
     * @return array
     */
    public static function getTime($date)
    {
        $dateArr = self::getDateBetween();

        if (isset($dateArr[$date]) && $date !== 'all') {
            $between = $dateArr[$date];

            return [strtotime($between[0]), strtotime($between[1])];
        }

        return [];
    }

    /*
     * 根据【年，周】，获取时间范围
     * @param int $year
     * @param int $week
     * @return string|false
     */
    public static function getWeekStartAndEnd ($year, $week) {
        $year = (int)$year;
        $week = (int)$week;
        $date = new \DateTime;

        // 一年介于 【52 - 53】 周
        $date->setISODate($year, 53);
        $weeks = max($date->format("W"),52);

        // 如果给定的周数大于周总数或小于等于0
        if($week > $weeks || $week <= 0){
            return false;
        }

        // 如果周数小于10，需前补0，否则错误
        if($week < 10){
            $week = '0' . $week;
        }

        // 当周起止时间戳,
        $startTime = strtotime($year . 'W' . $week);

        // 当周起止日期
        $startDate = date("Y.m.d", $startTime);
        $endDate = date("Y.m.d", strtotime('+1 week', $startTime) - 1);

        return $startDate . ' - ' . $endDate;
    }
}