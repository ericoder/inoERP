<?php
/**
 * inoERP
 *
 * @copyright   2014 Nishit R. Das
 * @license     https://www.mozilla.org/MPL/2.0/
 * @link        http://inoideas.org
 * @source code https://github.com/inoerp/inoERP
 */

/**
 * address
 * Contains all the address information, such as - address_id, type, description,  address_name, etc.
 * 
 */

class address extends dbObject {

 public static $table_name = "address";
  public static $primary_column = "address_id";
 public static $key_column = 'address_name';
 public static $module = "org";
  public static $system_info = [
  'name' => 'Address',
  'number' => '2006',
  'description' => 'Define and mainten addresses',
  'version' => '0.1.1',
  'db_version' => '1001',
  'mod_version' => '1.1.1',
  'primary_entity_cb' => '',
  'primary_entity_cb' => '',
  'module_name' => 'org',
  'weight' => 6
 ];
 public $field_a = [
		 "address_id",
		 "type",
		 "address_name",
		 'mdm_tax_region_id',
		 "description",
		 "phone",
		 "email",
		 "website",
		 "address",
		 "country",
		 "postal_code",
		 "default_cb",
		 "status",
		 "usage_type",
		 "created_by",
		 "creation_date",
		 "last_update_by",
		 "last_update_date"
 ];
 public $initial_search = [
		 "address_name",
		 "type",
		 "description",
		 "phone",
		 "email",
		 "website",
		 "country",
		 "postal_code"
 ];
 
 public $checkbox = [
		 "rev_enabled",
 ];
 
 
 public $requiredField = [
		 "address_name",
		 "type"
 ];
 
  public $fields_inForm_notInDataBase = [
		 "tax_region_name",
   'window_type'
 ];
  
   public static $json_label_fields = [
 "address_name",
		 "type",
		 "description",
		 "phone",
		 "email",
		 "website",
		 "country",
		 "postal_code"
 ];
	
   public $search = [
		 '_update_path' => 'form.php?class_name=address',
		 '_show_update_path' => 1,
		 '_update_action_meassge' => 'Update',
		 '_show_view_path' => 1,
//		 '_view_action_meassge' => 'View',
//		 '_extra_path' => '',
 ];
 public $pageTitle = " Address - Organization Addresses "; //page Title
 
 public $address_id;
 public $type;
 public $address_name;
 public $mdm_tax_region_id;
 public $description;
 public $phone;
 public $email;
 public $website;
 public $address;
 public $country;
 public $postal_code;
 public $default_cb;
 public $status;
 public $usage_type;
 public $rev_number;
 public $created_by;
 public $creation_date;
 public $last_update_by;
 public $last_update_date;
 public $time;
 public $msg;
 public $tax_region_name;
 public $window_type;

 public function _before_save(){
	if(!empty($this->tax_region_name)){
	 $this->mdm_tax_region_id = mdm_tax_region::find_by_keyColumn($this->tax_region_name)->mdm_tax_region_id;
	}
 }
 
  public function _before_showing(){
	if(!empty($this->mdm_tax_region_id)){
	 $this->tax_region_name = mdm_tax_region::find_by_id($this->mdm_tax_region_id)->tax_region_name;
	}
 }
 
 Public static function address_types() {
	$option_header = option_header::find_by_name('ADDRESS_TYPE');
	$type = option_line::find_by_option_id($option_header->option_header_id);
	return $type;
 }
 
  Public static function address_usage_type() {
	$option_header = option_header::find_by_name('ADDRESS_USAGE_TYPE');
	$type = option_line::find_by_option_id($option_header->option_header_id);
	return $type;
 }

   public static function profile_ship_to_address_default() {
   $line_data_array = [];
   $line_data_array['line_key'] = 'address_id';
   $line_data_array['line_desc'] = 'address_name';
   $line_data_array['line_values'] = address::find_all();
   return $line_data_array;
  }
  
     public static function profile_bill_to_address_default() {
   $line_data_array = [];
   $line_data_array['line_key'] = 'address_id';
   $line_data_array['line_desc'] = 'address_name';
   $line_data_array['line_values'] = address::find_all();
   return $line_data_array;
  }
  
 public static function find_by_address_name($address_name) {
	$sql = "SELECT * FROM " . self::$table_name . " where address_name REGEXP '{$address_name}$' LIMIT 1";
	$result_array = self::find_by_sql($sql);
	return !empty($result_array) ? array_shift($result_array) : false;
 }

 protected static function address_field(&$address, $field_name) {
	$field_statement = form::text_field($field_name, $address->$field_name, '20', '', '', '', $field_name, 1);
	return $field_statement;
 }

 public static function show_adrees_details(&$address = "") {
	$show_adrees_details = "";
	$show_adrees_details .= "<ul class=\"address inline_list\">";
	$show_adrees_details .= "<li><label>Phone  : </label>" . self::address_field($address, 'phone') . "</li>";
	$show_adrees_details .= "<li><label>Email  : </label>" . self::address_field($address, 'email') . "</li>";
	$show_adrees_details .= "<li><label>Web-site  : </label>" . self::address_field($address, 'website') . "</li>";
	$show_adrees_details .= "<li><label>Country  : </label>" . self::address_field($address, 'country') . "</li>";
	$show_adrees_details .= "<li><label>Postal Code  : </label>" . self::address_field($address, 'postal_code') . "</li>";
	$show_adrees_details .= "<li><label>Address :</label>" . form::text_area('address', $address->address) . "</li>";
	$show_adrees_details .= "</ul>";
	return $show_adrees_details;
 }

 public static function show_adrees_details_inLine(&$address) {
	if (empty($address)){
	 return ;
	}
	$show_adrees_details = $address->address . '<br>' . $address->postal_code . ','
					. $address->country . '<br>' . $address->phone
					. $address->email . '<br>' . $address->website;
	return $show_adrees_details;
 }

}

?>
