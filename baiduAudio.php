<?php
/*
Plugin Name: Hylsay Text Reading
Plugin URI: https://blog.aoaoao.info/post/wordpress-plugin-hylsay-text-reading/
Description: A plug-in that can read
Version: 3.1.3
Author: hylsay
Author URI: https://blog.aoaoao.info
*/

function hylsay_text_reading_admin_mycss() {
    echo '<style type="text/css">
    .form-table th {
		font-weight:400;
	}
    </style>';
 }
add_action('admin_head', 'hylsay_text_reading_admin_mycss');

class HylsayTextReadingPlugin {
	private $baiduaudio_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'baiduaudio_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'baiduaudio_page_init' ) );
	}

	public function baiduaudio_plugin_page() {
		add_menu_page(
			'文章阅读插件', // page_title
			'文章阅读插件', // menu_title
			'manage_options', // capability
			'baiduaudio', // menu_slug
			array( $this, 'baiduaudio_create_admin_page' ), // function
			'dashicons-admin-generic', // icon_url
			100 // position
		);
	}

	public function baiduaudio_create_admin_page() {
		$this->baiduaudio_options = get_option( 'baiduaudio_option_name' ); ?>

		<div class="wrap">
			<h2>文章阅读插件设置</h2>
			<p><b>插件介绍：</b></p>
			<p>本插件是基于百度语音合成开发，需要自行申请百度语音合成APIkey，地址：<a href="http://ai.baidu.com/tech/speech/" target="_blank">http://ai.baidu.com/tech/speech/</a></p>
			<p>问题反馈：<a href="https://blog.aoaoao.info/post/wordpress-plugin-hylsay-text-reading/" target="_blank">https://blog.aoaoao.info/post/wordpress-plugin-hylsay-text-reading/</a></p>
			<p><b>插件设置：</b></p>
			<p>1.初始设置。语速、音调、音量这三项，取值0-15，不填默认为5。</p>
			<p>2.声音类型。如果你购买的是基础音库，就选择基础语音对应的类型；如果是精品音库，就选择精品音库对应的类型。</p>
			<p>3.阅读范围。请填写正文div标签，例如：.entry-content。</p>
			<p>4.阅读屏蔽。如果不想阅读某个div中的内容，就把该div对应的标签填写进去，例如：.ads-google,.announce，如果需要屏蔽的较多切记使用逗号（英文半角）分开。</p>
			
            <?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'baiduaudio_option_group' );
					do_settings_sections( 'baiduaudio-admin' );
					submit_button();
				?>
			</form>
			<hr>
			<p>如果您觉得本插件还不错，并且方便了您，望多多给予支持，开发不易呀，谢谢！</p>
			
			<img src="https://img-blog.csdnimg.cn/36a8f46377ad419887e73ddc921906fc.png" alt="打赏是一种美德" width="450px" >
		</div>
	<?php }

	public function baiduaudio_page_init() {
		register_setting(
			'baiduaudio_option_group', // option_group
			'baiduaudio_option_name', // option_name
			array( $this, 'baiduaudio_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'baiduaudio_setting_section', // id
			'基础设置', // title
			array( $this, 'baiduaudio_section_info' ), // callback
			'baiduaudio-admin' // page
		);
		

		add_settings_field(
			'baidu_apiKey', // id
			'百度语音合成apiKey', // title
			array( $this, 'baidu_apiKey_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_secretKey', // id
			'百度语音合成secretKey', // title
			array( $this, 'baidu_secretKey_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'select_post_page', // id
			'应用范围', // title
			array( $this, 'select_post_page_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_spd', // id
			'语速', // title
			array( $this, 'baidu_spd_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_pit', // id
			'音调', // title
			array( $this, 'baidu_pit_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baidu_vol', // id
			'音量', // title
			array( $this, 'baidu_vol_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'select_shengyin', // id
			'选择声音类型', // title
			array( $this, 'select_shengyin_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baiduaudio_post_divtag', // id
			'阅读范围', // title
			array( $this, 'baiduaudio_post_divtag_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);

		add_settings_field(
			'baiduaudio_pingbi_divtag', // id
			'阅读屏蔽', // title
			array( $this, 'baiduaudio_pingbi_divtag_callback' ), // callback
			'baiduaudio-admin', // page
			'baiduaudio_setting_section' // section
		);
	
	}

	public function baiduaudio_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['baidu_apiKey'] ) ) {
			$sanitary_values['baidu_apiKey'] = sanitize_text_field( $input['baidu_apiKey'] );
		}

		if ( isset( $input['baidu_secretKey'] ) ) {
			$sanitary_values['baidu_secretKey'] = sanitize_text_field( $input['baidu_secretKey'] );
		}

		// 选择页面
		if ( isset( $input['select_post_page'] ) ) {
			$sanitary_values['select_post_page'] = sanitize_text_field( $input['select_post_page'] );
		}

		// 语速
		if ( isset( $input['baidu_spd'] ) ) {
			$sanitary_values['baidu_spd'] = sanitize_text_field( $input['baidu_spd'] );
		}
		// 音调
		if ( isset( $input['baidu_pit'] ) ) {
			$sanitary_values['baidu_pit'] = sanitize_text_field( $input['baidu_pit'] );
		}
		//音量
		if ( isset( $input['baidu_vol'] ) ) {
			$sanitary_values['baidu_vol'] = sanitize_text_field( $input['baidu_vol'] );
		}

		if ( isset( $input['select_shengyin'] ) ) {
			$sanitary_values['select_shengyin'] = $input['select_shengyin'];
		}

		if ( isset( $input['baiduaudio_post_divtag'] ) ) {
			$sanitary_values['baiduaudio_post_divtag'] = sanitize_text_field( $input['baiduaudio_post_divtag'] );
		}

		if ( isset( $input['baiduaudio_pingbi_divtag'] ) ) {
			$sanitary_values['baiduaudio_pingbi_divtag'] = sanitize_text_field( $input['baiduaudio_pingbi_divtag'] );
		}

		return $sanitary_values;
	}

	public function baiduaudio_section_info() {
		echo '<hr>';
	}

	public function select_post_page_callback() {
		?> <fieldset>
		<?php $checked = ( isset( $this->baiduaudio_options['select_post_page'] ) && $this->baiduaudio_options['select_post_page'] === '0' ) ? 'checked' : '' ; ?>
		<label for="select_post_page-0"><input type="radio" name="baiduaudio_option_name[select_post_page]" id="select_post_page-0" value="0" checked > 文章</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_post_page'] ) && $this->baiduaudio_options['select_post_page'] === '1' ) ? 'checked' : '' ; ?>
		<label for="select_post_page-1"><input type="radio" name="baiduaudio_option_name[select_post_page]" id="select_post_page-1" value="1" <?php echo $checked; ?>> 页面</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_post_page'] ) && $this->baiduaudio_options['select_post_page'] === '2' ) ? 'checked' : '' ; ?>
		<label for="select_post_page-2"><input type="radio" name="baiduaudio_option_name[select_post_page]" id="select_post_page-2" value="2" <?php echo $checked; ?>> 文章+页面</label>

		</fieldset> <?php
	}

	public function select_shengyin_callback() {
		?> <fieldset>
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '3' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-2"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-2" value="3" checked > 基础音库-度逍遥</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '0' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-0"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-0" value="0" <?php echo $checked; ?>> 度小美</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '1' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-1"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-1" value="1" <?php echo $checked; ?>> 度小宇</label> &nbsp;&nbsp;
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '4' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-3"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-3" value="4" <?php echo $checked; ?>> 度丫丫</label><br>

		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '5003' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-4"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-4" value="5003" <?php echo $checked; ?>> 精品音库-度逍遥</label> &nbsp;&nbsp;

		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '5118' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-5"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-5" value="5118" <?php echo $checked; ?>> 度小鹿</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '106' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-6"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-6" value="106" <?php echo $checked; ?>> 度博文</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '110' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-7"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-7" value="110" <?php echo $checked; ?>> 度小童</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '111' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-8"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-8" value="111" <?php echo $checked; ?>> 度小萌</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '103' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-9"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-9" value="103" <?php echo $checked; ?>> 度米朵</label> &nbsp;&nbsp;
		
		<?php $checked = ( isset( $this->baiduaudio_options['select_shengyin'] ) && $this->baiduaudio_options['select_shengyin'] === '5' ) ? 'checked' : '' ; ?>
		<label for="select_shengyin-10"><input type="radio" name="baiduaudio_option_name[select_shengyin]" id="select_shengyin-10" value="5" <?php echo $checked; ?>> 度小娇</label>

		</fieldset> <?php
	}

	public function baidu_apiKey_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_apiKey]" id="baidu_apiKey" value="%s">',
			isset( $this->baiduaudio_options['baidu_apiKey'] ) ? esc_attr( $this->baiduaudio_options['baidu_apiKey']) : ''
		);
	}

	public function baidu_secretKey_callback() {
		printf(
			'<input class="regular-text" type="password" name="baiduaudio_option_name[baidu_secretKey]" id="baidu_secretKey" value="%s">',
			isset( $this->baiduaudio_options['baidu_secretKey'] ) ? esc_attr( $this->baiduaudio_options['baidu_secretKey']) : ''
		);
	}
	//语速
	public function baidu_spd_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_spd]" id="baidu_spd" value="%s" placeholder="取值0-15，不填默认为5，中语速">',
			isset( $this->baiduaudio_options['baidu_spd'] ) ? esc_attr( $this->baiduaudio_options['baidu_spd']) : ''
		);
	}

	//音调
	public function baidu_pit_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_pit]" id="baidu_pit" value="%s" placeholder="取值0-15，不填默认为5，中语调">',
			isset( $this->baiduaudio_options['baidu_pit'] ) ? esc_attr( $this->baiduaudio_options['baidu_pit']) : ''
		);
	}

	//音量
	public function baidu_vol_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baidu_vol]" id="baidu_vol" value="%s" placeholder="取值0-15，不填默认为5，中音量">',
			isset( $this->baiduaudio_options['baidu_vol'] ) ? esc_attr( $this->baiduaudio_options['baidu_vol']) : ''
		);
	}

	public function baiduaudio_post_divtag_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baiduaudio_post_divtag]" id="baiduaudio_post_divtag" value="%s" placeholder="例如：.entry-content">',
			isset( $this->baiduaudio_options['baiduaudio_post_divtag'] ) ? esc_attr( $this->baiduaudio_options['baiduaudio_post_divtag']) : ''
		);
	}

	public function baiduaudio_pingbi_divtag_callback() {
		printf(
			'<input class="regular-text" type="text" name="baiduaudio_option_name[baiduaudio_pingbi_divtag]" id="baiduaudio_pingbi_divtag" value="%s" placeholder="例如：.ads-google，自定义div标签等">',
			isset( $this->baiduaudio_options['baiduaudio_pingbi_divtag'] ) ? esc_attr( $this->baiduaudio_options['baiduaudio_pingbi_divtag']) : ''
		);
	}


}
if ( is_admin() )
	$baiduaudio = new HylsayTextReadingPlugin();


function add_hylsay_text_reading_js($hook) {
 
	$my_js_ver  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'js/baiduAudio.js' ));
	$my_js_query  = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'js/jquery.min.js' ));
	$my_css_ver = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'css/baiduAudio.css' ));
	$my_css_fontawesome = date("ymd-Gis", filemtime( plugin_dir_path( __FILE__ ) . 'css/font-awesome-4.7.0/css/font-awesome.min.css' ));
	 
	wp_enqueue_script( 'query_js', plugins_url( 'js/jquery.min.js', __FILE__ ), array(), $my_js_query );
    wp_enqueue_script( 'baiduAudio_js', plugins_url( 'js/baiduAudio.js', __FILE__ ), array(), $my_js_ver );
    wp_register_style( 'baiduAudio_css',    plugins_url( 'css/baiduAudio.css',    __FILE__ ), false,   $my_css_ver );
	wp_enqueue_style ( 'baiduAudio_css' );
	
	wp_register_style( 'fontawesome_css',    plugins_url( 'css/font-awesome-4.7.0/css/font-awesome.min.css',    __FILE__ ), false,   $my_css_fontawesome );
    wp_enqueue_style ( 'fontawesome_css' );
 
}
add_action('wp_enqueue_scripts', 'add_hylsay_text_reading_js');

//注册一个路由
add_action('rest_api_init', function () {
	register_rest_route('hylsaytextreading', '/hylsay_text_reading_get_baiduAudio_token/', array('methods' => 'get', 'callback' => 'hylsay_text_reading_get_baiduAudio_token',));
});

function hylsay_text_reading_get_baiduAudio_token() {
	
	$baiduaudio_options = get_option( 'baiduaudio_option_name' );
	$apiKey = $baiduaudio_options['baidu_apiKey'];
	$secretKey = $baiduaudio_options['baidu_secretKey'];
	$baidu_spd = $baiduaudio_options['baidu_spd']?:5;
	$baidu_pit = $baiduaudio_options['baidu_pit']?:5;
	$baidu_vol = $baiduaudio_options['baidu_vol']?:5;

	$api = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=' . $apiKey . '&client_secret=' . $secretKey;
	$api = wp_remote_get( $api );

	$result = wp_remote_retrieve_body( $api );
	$result_obj = json_decode($result);

	$access_token = $result_obj->access_token;
	$expires_in = $result_obj->expires_in;
	$session_key = $result_obj->session_key;

	$getper = $baiduaudio_options['select_shengyin'];
	$getposttag = $baiduaudio_options['baiduaudio_post_divtag'];
	$getpingbitag = $baiduaudio_options['baiduaudio_pingbi_divtag'];
	$return = array('access_token' => $access_token, 'session_key' => $session_key, 'spd' =>  $baidu_spd, 'pit' =>  $baidu_pit, 'vol' => $baidu_vol, 'per' =>  $getper,'yuedu_posttag' => $getposttag,'yuedu_pingbitag' => $getpingbitag);
	return $return;
}

function hylsay_text_reading_baidu_ai_audio_content($content) {
	$baiduaudio_options = get_option( 'baiduaudio_option_name' );
	$select_post_page = $baiduaudio_options['select_post_page'];

	if ($select_post_page == 0) {
		if (is_single()) {
			return '<div class="baiduAudioWrap"></div><span class="hylsay-text-r-info"></span>' . $content;
		}
	}elseif ($select_post_page == 1) {
		if (is_page()) {
			return '<div class="baiduAudioWrap"></div><span class="hylsay-text-r-info"></span>' . $content;
		}
	}elseif ($select_post_page == 2) {
		if (is_single() || is_page()) {
			return '<div class="baiduAudioWrap"></div><span class="hylsay-text-r-info"></span>' . $content;
		}
	}
	
	return $content;
}
add_filter("the_content", "hylsay_text_reading_baidu_ai_audio_content");