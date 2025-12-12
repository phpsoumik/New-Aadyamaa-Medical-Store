<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateFilters extends Model
{
    use HasFactory;

    private $filter = '';
    private $date1 = '';
    private $date2 = '';

    private $filterList = array(
        array('key' => "None",'value' => "None" ),
        array('key' => "Today",'value' => "today" ),
        array('key' => "This Week",'value' => "this_week" ),
        array('key' => "This Month",'value' => "this_month" ),
        array('key' => "Last Week",'value' => "last_week" ),
        array('key' => "Last Month",'value' => "last_month" ),
        array('key' => "Next Week",'value' => "next_week" ),
        array('key' => "Next Month",'value' => "next_month" ),
        array('key' => "Last Three Months",'value' => "last_three" ),
        array('key' => "This Year",'value' => "this_year" ),
        array('key' => "Last Year",'value' => "last_year" )
    );

    public function get($name)
    {
        return $this->$name;
    }

    public function set($name, $value)
    {
        $this->$name = $value;
    }
    
    public function getTheDates()
    {
        $dates = [];

        if($this->filter != 'None')
		{
			if($this->filter == 'today')
			{
				$date1 = date('Y-m-d');
				$date2 = date('Y-m-d');
			}
			else if($this->filter == 'this_week')
			{
				$d = strtotime("today");

				$start_week = strtotime("last saturday midnight",$d);
				$end_week = strtotime("thursday",$d);

				$date1 = date("Y-m-d",$start_week); 
				$date2 = date("Y-m-d",$end_week);  
			}
			else if($this->filter == 'this_month')
			{
				$date1 = date('Y-m-d', strtotime('first day of this month'));
				$date2 = date('Y-m-d', strtotime('last day of this month'));
			}
			else if($this->filter == 'last_week')
			{
				$d = strtotime("-1 week -1 day");
				$start_week = strtotime("last saturday midnight",$d);
				$end_week = strtotime("thursday",$d);

				$date1 = date("Y-m-d",$start_week); 
				$date2 = date("Y-m-d",$end_week); 
			}
			else if($this->filter == 'last_month')
			{
				$date1 = date("Y-m-d", strtotime("first day of previous month"));
				$date2 = date("Y-m-d", strtotime("last day of previous month"));
			}
			else if($this->filter == 'next_week')
			{
				$d = strtotime("+1 week -1 day");
				$start_week = strtotime("last sunday midnight",$d);
				$end_week = strtotime("next saturday",$d);

				$date1 = date("Y-m-d",$start_week); 
				$date2 = date("Y-m-d",$end_week); 
			}
			else if($this->filter == 'next_month')
			{
				$date1 = date('Y-m-d',strtotime('first day of +1 month'));
				$date2 = date('Y-m-d',strtotime('last day of +1 month'));
			}
			else if($this->filter == 'last_three')
			{
				$date1 = date('Y-m-d',strtotime('first day of -3 month'));
				$date2 = date('Y-m-d',strtotime('last day of -1 month'));
			}
			else if($this->filter == 'this_year')
			{
				$date1 = date('Y-m-d', strtotime('first day of january this year'));
				$date2 = date('Y-m-d', strtotime('last day of december this year'));
			}
			else if($this->filter == 'last_year')
			{
				$date1 = date('Y-m-d', strtotime('first day of january last year'));
				$date2 = date('Y-m-d', strtotime('last day of december last year'));
			}

            $dates = [$date1,$date2];
		}
		else if($this->date1 != NULL AND  $this->date2 != NULL)
		{
			$dates = [$this->date1,$this->date2];
		}
		else
		{
			$date1 = date('Y-m-d', strtotime('first day of this month'));
			$date2 = date('Y-m-d', strtotime('last day of this month'));
			$dates = [$date1,$date2];
		}
		
        return  $dates;
    }
	
	public function getTheMonthDates()
    {
        $dates = [];

        if($this->filter != 'None')
		{
			
			if($this->filter == 'this_month')
			{
				$date1 = date('Y-m-d', strtotime('first day of this month'));
				$date2 = date('Y-m-d', strtotime('last day of this month'));
			}
			
			else if($this->filter == 'last_month')
			{
				$date1 = date("Y-m-d", strtotime("first day of previous month"));
				$date2 = date("Y-m-d", strtotime("last day of previous month"));
			}

            $dates = [$date1,$date2];
		}
		else if($this->date1 != NULL)
		{
			$date1 = date("Y-m-01", strtotime($this->date1));
			$date2 = date("Y-m-t", strtotime($this->date1));
			$dates = [$date1,$date2];
		}
		else
		{
			$date1 = date('Y-m-d', strtotime('first day of this month'));
			$date2 = date('Y-m-d', strtotime('last day of this month'));
			$dates = [$date1,$date2];
		}
		
        return  $dates;
    }
}
