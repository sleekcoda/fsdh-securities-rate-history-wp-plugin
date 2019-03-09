<?php
class RCRateHistory{
    function __construct() 
    {
		/* 
		 * Hook object functions into WorPress 
		 */ 
        add_action('admin_init',array('RCRateHistory','add_settings'));
        add_action('admin_init',array('RCRateHistory','plugin_activate'));
        add_action('admin_menu',array('RCRateHistory','add_menu_page'));
        add_action('admin_enqueue_scripts',array('RCRateHistory','add_scripts'));
		add_action('wp_ajax_rc_rate_history',array('RCRateHistory','process_rate_history_form'));
		wp_localize_script('rc_rate_history_js','rc_script_obj',array(
			'ajaxurl'	=> admin_url('admin_ajax.php')
		)); 
        register_activation_hook(__FILE__,array('RCRateHistory','plugin_activate'));
        register_deactivation_hook(__FILE__,array('RCRateHistory','plugin_deactivate'));
		/* 
		 * Hook object functions into WorPress 
		 */ 
		add_shortcode('fsdh_lsp', array('RCRateHistory','share_price_shortcode'));
		add_shortcode('fsdh_latest_share_price',array('RCRateHistory','get_latest_record'));

	}	
	public static function share_price_shortcode( $atts ) {
		$data = self::get_latest_record();
		if(isset($atts['get']) && $atts['get']=='date'):
			$atts['get'] = date('D, M j, Y', strtotime($data->rate_date));
			return "{$atts['get']}";
		elseif(isset($atts['get']) && $atts['get']=='cgfb'):
			$atts['get'] = (string)$data->coral_growth_fund_bid;
			return "{$atts['get']}";
		elseif(isset($atts['get']) && $atts['get']=='cgfo'):
			$atts['get'] = $data->coral_growth_fund_offer;
			return "{$atts['get']}";
		elseif(isset($atts['get']) && $atts['get']=='cifb'):
			$atts['get'] = $data->coral_income_fund_bid;
			return "{$atts['get']}";
		elseif(isset($atts['get']) && $atts['get']=='cifo'):
			$atts['get'] = $data->coral_income_fund_offer;
			return "{$atts['get']}";
		endif;	
	}
	public static function add_menu_page(){
		add_menu_page( "Rate History", "Rate History", "edit_posts", "rc_rate_history", array('RCRateHistory','admin_view'),'dashicons-chart-pie',10);
	}
    public static function plugin_activate(){
    	self::create_table();
        flush_rewrite_rules();
    }

    public static function plugin_deactivat(){
        flush_rewrite_rules();
    }
    public static function add_scripts($hook){
        /**
         * Add JS files
         */

        wp_register_script('rc_rate_history_js',plugins_url('/rc-rate-history/css.js/admin_script.js'),array('jquery'),10,true);
		wp_register_script('rc_jquery_validate',plugins_url('/css.js/validate.js',__FILE__),array(jquery),10,true);
		
		wp_enqueue_script( 'jquery-ui-datepicker',array('jquery') );
		wp_enqueue_script('rc_jquery_validate');
		wp_enqueue_script('rc_rate_history_js');
        /**
         * Add CSS files
         */
    }
    public static function admin_view(){
        include_once('html/admin_view.html');
    }

    public static function add_settings(){
        //register_settings('rcrate_history_group','');
    }
	public static function process_rate_history_form(){
		if(!current_user_can('edit_posts')):
			wp_die("Unauthorize user");
		endif;
		if(!wp_verify_nonce($_POST['rc_rate_nonce'], 'rc_rate_history')):
			wp_die('Form validation error');
		endif;
		if(!isset($_POST['growth_fb']) || !isset($_POST['growth_fo']) || !isset($_POST['income_fb']) || !isset($_POST['income_fo']) || !isset($_POST['updcreit'])){
			wp_die("Empty data recieve");
		}
		$date = esc_attr($_POST['date']) ;
		$coral_growth_fb = (float)esc_attr($_POST['growth_fb']);
		$coral_growth_fo = (float) esc_attr($_POST['growth_fo']) ;
		$coral_income_fb = (float)esc_attr($_POST['income_fb']) ;
		$coral_income_fo = (float)esc_attr($_POST['income_fo']) ;
		$updc = (float)esc_attr($_POST['updcreit']) ;
		self::update_table($date,$coral_growth_fb,$coral_growth_fo,$coral_income_fb,$coral_income_fo,$updc);
		echo "Company: $coral_growth_fb \n Date: $date \n Offer: $coral_growth_fo - $coral_growth_fo \n Bid Bid: $coral_income_fb - $coral_income_fb";
	}


	public static function create_table() {
   		global $wpdb;
   		$table_name = $wpdb->prefix . "historical_prices";
		$charset = $wpdb->get_charset_collate();
		
		 #Check to see if the table exists already, if not, then create it

		if($wpdb->get_var( "SHOW TABLES LIKE $table_name" ) != $table_name) 
		{

			$sql = "CREATE TABLE {$table_name} ( ";			
			$sql .= "`id` INT NOT NULL AUTO_INCREMENT, ";
			$sql .= "`rate_date` DATE NOT NULL , ";
			$sql .= "`coral_growth_fund_bid` FLOAT NOT NULL , ";
			$sql .= "`coral_growth_fund_offer` FLOAT NOT NULL , ";
			$sql .= "`coral_income_fund_bid` FLOAT NOT NULL, ";
			$sql .= "`coral_income_fund_offer` FLOAT NOT NULL , ";
			$sql .= "`updc_reit` FLOAT NOT NULL , ";
			$sql .= "PRIMARY KEY (`id`)) ";
			$sql .= "{$charset};";
			
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			$details = dbDelta($sql);
			/*
			This is for debug purpose
			
			foreach($details as $detail):
				printf("<h1><error>%s helllllllllllllllllllllllllllllllllllllll</error><h1>",$detail);
			endforeach;*/
		}
		
	}
	
	private static function update_table($date,$cgfb,$cgfo,$cifb,$cifo,$updc){
   		global $wpdb;
   		$table_name = $wpdb->prefix . "historical_prices";
		$wpdb->insert( 
			$table_name, 
			array(
				'id'	=>	'',
				'rate_date' => $date, 
				'coral_growth_fund_bid' => $cgfb, 
				'coral_growth_fund_offer' => $cgfo, 
				'coral_income_fund_bid' => $cifb, 
				'coral_income_fund_offer' => $cifo, 
				'updc_reit' => $updc, 
			) 
		);
	}
	
	public static function get_latest_record(){
		
		
		global $wpdb;
		$tablename = $wpdb->prefix.'historical_prices';
		$data = $wpdb->get_row("SELECT * FROM `$tablename` WHERE id=(SELECT MAX(id) FROM `$tablename`)");
		return $data;
		/*$html = "<div class=\"hp_container\">";
		$html .= "<div class=\"hp_content\">";
		
		$html .= "<div class=\"hp_group\">";
    		$html .= '<p class="hp_title" style="">'.date('D, M j, Y', strtotime($data->rate_date)).'</p>';
    		$html .= '<h3 class="hp_price" style="">'.$data->coral_growth_fund_bid.'</h3>';
    		$html .= '<p class="hp_description" style="">CGF Bid Rate </p>';
		$html .= '</div>';
		
		$html .= "<div class=\"hp_group\">";
    		$html .= '<p class="hp_title" style="">'.date('D, M j, Y', strtotime($data->rate_date)).'</p>';
    		$html .= '<h3 class="hp_price" style="">'.$data->coral_growth_fund_offer.'</h3>';
    		$html .= '<p class="hp_description" style="">CGF Offer Rate </p>';
		$html .= '</div>';
		
		$html .= "<div class=\"hp_group\">";
    		$html .= '<p class="hp_title" style="">'.date('D, M j, Y', strtotime($data->rate_date)).'</p>';
    		$html .= '<h3 class="hp_price" style="">'.$data->coral_income_fund_bid.'</h3>';
    		$html .= '<p class="hp_description" style="">CIF Bid Rate </p>';
		$html .= '</div>';
		
		$html .= "<div class=\"hp_group\">";
    		$html .= '<p class="hp_title" style="">'.date('D, M j, Y', strtotime($data->rate_date)).'</p>';
    		$html .= '<h3 class="hp_price" style="">'.$data->coral_income_fund_offer.'</h3>';
    		$html .= '<p class="hp_description" style="">CIF Offer Rate </p>';
		$html .= '</div>';
		 
		$html .= "<div class=\"hp_group\">";
    		$html .= '<p class="hp_title" style="">'.date('D, M j, Y', strtotime($data->rate_date)).'</p>';
    		$html .= '<h3 class="hp_price" style="">'.$data->updc_reit.'</h3>';
    		$html .= '<p class="hp_description" style="">UPDC REIT Offer </p>';
		$html .= '</div>';
		$html .= '</div>'; // .hp_content end
		$html .= '</div>'; // .hp_container end
		echo  $html; */
		
	}

}