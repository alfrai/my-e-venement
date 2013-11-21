<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * liOfcExample actions.
 *
 * @package     liOfcPlugin
 * @author      Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 * @since       27 mai 2013
 */

class liOfcExampleActions extends sfActions
{
	/**
	 * Executes index action (demo)
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeIndex(sfWebRequest $request)
	{
	}

	/**
	 * Creates a pie chart from random data
	 */
	public function executePieChartData()
	{
		$chatData = array();
		for( $i = 0; $i < 7; $i++ )
		{
			$data[] = rand(5,20);
		}

		//Creating a liGraph object		
		$g = new liGraph();

		//set background color
		$g->bg_colour = '#E4F5FC';

		//Set the transparency, line colour to separate each slice etc.
		$g->pie(80,'#78B9EC','{font-size: 12px; color: #78B9EC;');

		//array two arrray one containing data while other contaning labels 
		$g->pie_values($data, array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'));
		
		//Set the colour for each slice. Here we are defining three colours 
		//while we need 7 colours. So, the same colours will be 
		//repeated for the all remaining slices in the same order  
		$g->pie_slice_colours( array('#d01f3c','#356aa0','#c79810') );

		//To display value as tool tip
		$g->set_tool_tip( '#val#%' );

		$g->title( 'liOfcPlugin example', '{font-size:18px; color: #18A6FF}' );
		echo $g->render();
		return sfView::NONE;
	}

	/**
	 * Creates a bar chart from random data
	 *
	 */
	public function executeBarChartData()
	{
		$chartData = array();

		//Array with sample random data
		for( $i = 0; $i < 7; $i++ )
		{
			$chartData[] = rand(1,20);
		}

		//To create a bar chart we need to create a liBarOutline Object
		$bar = new liBarOutline( 80, '#78B9EC', '#3495FE' );
		$bar->key( 'Number of downloads per day', 10 );

		//Passing the random data to bar chart
		$bar->data = $chartData;

		//Creating a liGraph object
		$g = new liGraph();
		$g->title( 'liOfcPlugin example', '{font-size: 20px;}' );
		$g->bg_colour = '#E4F5FC';
		$g->set_inner_background( '#E3F0FD', '#CBD7E6', 90 );
		$g->x_axis_colour( '#8499A4', '#E4F5FC' );
		$g->y_axis_colour( '#8499A4', '#E4F5FC' );

		//Pass liBarOutline object i.e. $bar to graph
		$g->data_sets[] = $bar;

		//Setting labels for X-Axis
		$g->set_x_labels(array( 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday' ));

		// to set the format of labels on x-axis e.g. font, color, step
		$g->set_x_label_style( 10, '#18A6FF', 0, 2 );

		// To tick the values on x-axis
		// 2 means tick every 2nd value
		$g->set_x_axis_steps( 2 );

		//set maximum value for y-axis
		//we can fix the value as 20, 10 etc.
		//but its better to use max of data
		$g->set_y_max( max($chartData) );
		$g->y_label_steps( 4 );
		$g->set_y_legend( 'liOfcPlugin', 12, '#18A6FF' );
		echo $g->render();

		return sfView::NONE;
	}

	/**
	 * Creates line chart from random data.
	 */
	public function executeLineChartData()
	{
		$chartData = array();
		for( $i = 0; $i < 7; $i++ )
		{
			$chartData[] = rand(0, 50);
		}

		//Create new liGraph object
		$g = new liGraph();

		// Chart Title
		$g->title( 'liOfcPlugin example', '{font-size: 20px;}' );
		$g->bg_colour = '#E4F5FC';
		$g->set_inner_background( '#E3F0FD', '#CBD7E6', 90 );
		$g->x_axis_colour( '#8499A4', '#E4F5FC' );
		$g->y_axis_colour( '#8499A4', '#E4F5FC' );

		//Use line_dot to set line dots diameter, text, color etc.
		$g->line_dot(2, 3, '#3495FE', 'Number of downloads per day', 10);

		//In case of line chart data should be passed to liGraph object
		//unsing set_data
		$g->set_data( $chartData );

		//Setting labels for X-Axis
		$g->set_x_labels( array( 'Mon','Tue','Wed','Thu','Fri','Sat','Sun' ) );

		//to set the format of labels on x-axis e.g. font, color, step
		$g->set_x_label_style( 10, '#18A6FF', 0, 1 );

		//set maximum value for y-axis
		//we can fix the value as 20, 10 etc.
		//but its better to use max of data
		$g->set_y_max( max($chartData) );

		$g->y_label_steps( 5 );

		// display the data
		echo $g->render();

		echo $g->render();

		return sfView::NONE;
	}

	/**
	 * Creates a bar chart from random data
	 *
	 */
	public function execute3DBarChartData()
	{
		//Create new liBar3D object and set the transparency and colour.
		$redBar = new liBar3D( 75, '#d01f3c' );
		$redBar->key( '2007', 10 );
		
		//random data
		for( $i = 0; $i < 12; $i++ )
		{
			$redBar->data[] = rand(200,500);
		}

		//2nd Bar
		$blueBar = new liBar3D( 75, '#356aa0' );
		$blueBar->key( '2008', 10 );

		//random data for 2nd bar
		for( $i = 0; $i < 12; $i++ )
		{
			$blueBar->data[] = rand(200,500);
		}

		$g = new liGraph();
		$g->bg_colour = '#E4F5FC';
		$g->title( 'Number of downloads in 2008 and 2009', '{font-size:20px; color: #18A6FF;}' );

		$g->data_sets[] = $redBar;
		$g->data_sets[] = $blueBar;

		//to create 3d x-axis
		$g->set_x_axis_3d( 10 );
		$g->x_axis_colour( '#8499A4', '#E4F5FC' );
		$g->y_axis_colour( '#8499A4', '#E4F5FC' );
		
		$g->set_x_labels( array( 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct', 'Nov', 'Dec' ) );
		$g->set_y_max( 500 );
		$g->y_label_steps( 5 );
		$g->set_y_legend( 'liOfcPlugin', 12, '#18A6FF' );
		echo $g->render();

		return sfView::NONE;
	}
}
