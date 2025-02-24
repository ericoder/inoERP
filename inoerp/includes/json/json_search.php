<?php include_once("../basics/basics.inc"); ?>
<?php

if (!empty($_GET['class_name'])) {

 $class = $class_names = $_GET['class_name'];
 $$class = new $class;
 $table_name = empty($table_name) ? $class::$table_name : $table_name;
 $primary_column = property_exists($$class, 'primary_column') ? $class::$primary_column : $table_name . '_id';
 $pageno = !(empty($_GET['pageno'])) ? (int) $_GET['pageno'] : 1;
 $per_page = !(empty($_GET['per_page'])) ? (int) $_GET['per_page'] : 0;

 $_GET = get_postArray_From_jqSearializedArray($_GET['search_parameters']);
 $_GET['pageno'] = $pageno;
 $_GET['class_name'] = $class;
 $_GET['per_page'] = $per_page;

 if (empty($_GET['per_page'])) {
  if (!empty($_GET['per_page']) && ($_GET['per_page'] == "all" || $_GET['per_page'][0] == "all")) {
   $per_page = 50000;
  } else if (!empty($_GET['per_page'])) {
   $per_page = (is_array($_GET['per_page'])) ? $_GET['per_page'][0] : $_GET['per_page'];
  }
  $per_page = empty($per_page) ? 10 : $per_page;
 }
 $search_order_by = !(empty($_GET['search_order_by'])) ? $_GET['search_order_by'][0] : '';
 $search_asc_desc = !(empty($_GET['search_asc_desc'])) ? $_GET['search_asc_desc'][0] : '';
 echo '<div id="json_search_result">';

 //start search
 $search_array = $$class->field_a;
// pa($search_array);
 global $search_result_statement;

 //pre populate
 if (method_exists($$class, 'search_pre_populate')) {
  $ppl = call_user_func(array($$class, 'search_pre_populate'));
  if (!empty($ppl)) {
   foreach ($ppl as $search_key => $search_val) {
    $_GET[$search_key] = $search_val;
   }
  }
 }

 //column details
 if (empty($_GET['column_array'][0])) {
  if (property_exists($class, 'column')) {
   $column_array = $$class->column;
  } else {
   $column_array = $$class->field_a;
  }
 } else {
  $column_array = unserialize(base64_decode($_GET['column_array'][0]));
 }
 if (!empty($_GET['new_column'][0])) {
  $new_column = $_GET['new_column'][0];
  if (!empty($new_column)) {
   foreach ($new_column as $key => $value) {
    if ((!(is_array($value))) && (!empty($value))) {
     array_push($column_array, $value);
    }
   }
  }
 }

 $whereFields = [];
 $sqlWhereFields = [];
 $existing_search = [];
//to check number of criterias
 $noof_criteria = 0;

 foreach ($search_array as $key => $value) {
  if (!empty($_GET[$value][0])) {
   array_push($existing_search, $value);
   if (strpos($_GET[$value][0], ',') != false) {
    $comma_sep_search_parameters = explode(',', trim($_GET[$value][0]));
    $comma_sep_search_parameters_in_str = '(\'' . implode('\', \'', $comma_sep_search_parameters) . '\')';
    $whereFields[] = sprintf("%s IN %s ", $value, $comma_sep_search_parameters_in_str);
   } else {
    $entered_search_criteria = str_replace(' ', '%', trim($_GET[$value][0]));
    if (strpos($value, '_ID') !== false || strpos($value, '_id') !== false) {
     if ($entered_search_criteria == 'null') {
      $whereFields[] = " $value IS NULL ";
     } else {
      $whereFields[] = sprintf("%s = %s ", $value, trim($entered_search_criteria));
     }
    } else if (substr($entered_search_criteria, 0, 1) == '=') {
     if ($entered_search_criteria == 'null') {
      $whereFields[] = " $value IS NULL ";
     } else {
      $whereFields[] = sprintf("%s = '%s' ", $value, trim(substr($entered_search_criteria, 1)));
     }
    } else if (substr($entered_search_criteria, 0, 2) == '!=') {
     $whereFields[] = sprintf("%s != '%s' ", $value, trim(substr($entered_search_criteria, 2)));
    } else if (substr($entered_search_criteria, 0, 1) == '>') {
     $whereFields[] = sprintf("%s > '%s' ", $value, trim(substr($entered_search_criteria, 1)));
    } else if (substr($entered_search_criteria, 0, 1) == '<') {
     $whereFields[] = sprintf("%s < '%s' ", $value, trim(substr($entered_search_criteria, 1)));
    } else {
     $whereFields[] = sprintf("%s LIKE '%%%s%%'", $value, trim(mysql_prep($entered_search_criteria)));
    }
   }
   $noof_criteria++;
  }
  if (!empty($_GET[$value][1])) {
   array_push($existing_search, $value);
   if (substr($_GET[$value][1], 0, 1) == '>') {
    $whereFields[] = sprintf("%s > '%s' ", $value, trim(substr($_GET[$value][1], 1)));
   } else if (substr($_GET[$value][1], 0, 1) == '<') {
    $whereFields[] = sprintf("%s < '%s' ", $value, trim(substr($_GET[$value][1], 1)));
   }
   $noof_criteria++;
  }
 }

 if ((!empty($_GET['report_name'])) && method_exists($$class, $_GET['report_name'][0])) {
  $report_name = $_GET['report_name'][0];
  $search_result_statement = call_user_func(array($$class, $report_name), $_GET);
  echo '<div id="searchResult"><div id="search_result" class="search_report">';
  echo '<ul class="inline-block"> <li id="export_excel_searchResult" class="clickable" title="Export to Excel"><i class="fa fa-file-excel-o"></i></li>
              <li id="print_searchResult" class=" print clickable" title="Print"><i class="fa fa-print"></i></li>
             </ul>';
  echo $search_result_statement;
  echo '</div></div></div>';
  return;
 } else if ((!empty($_GET['function_name']))) {
  $function_name = is_array($_GET['function_name']) ? $_GET['function_name'][0] : $_GET['function_name'];
  if (method_exists($$class, $function_name)) {
   $search_details = call_user_func(array($$class, $function_name), $_GET);
   $search_counts = $function_name . '_search_counts';
   $search_records = $function_name . '_search_records';
   $search_downloads = $function_name . '_search_downloads';
   $whereClause = implode(" AND ", $whereFields);
   $_GET['whereClause'] = $whereClause;
   $total_count = call_user_func(array($$class, $search_counts), $_GET);
   $search_result = call_user_func(array($$class, $search_records), $_GET);
   $all_download_sql = call_user_func(array($$class, $search_downloads), $_GET);
   if (!empty($per_page) && !empty($total_count)) {
    $pagination = new pagination($pageno, $per_page, $total_count);
    $pagination_statement = $pagination->show_pagination();
   }
  }
 } else if (method_exists($$class, 'search_records')) {
  $whereClause = implode(" AND ", $whereFields);
  $_GET['whereClause'] = $whereClause;
  $total_count = call_user_func(array($$class, 'search_counts'), $_GET);
  $search_result = call_user_func(array($$class, 'search_records'), $_GET);
  $all_download_sql = call_user_func(array($$class, 'search_downloads'), $_GET);
  if (!empty($per_page) && !empty($total_count)) {
   $pagination = new pagination($pageno, $per_page, $total_count);
   $pagination_statement = $pagination->show_pagination();
  }
 } else {
  //validate organization access where applicable
  if (property_exists($$class, 'bu_org_id') && in_array('bu_org_id', $$class->field_a)) {
   if (empty($_SESSION['user_org_access'])) {
    $no_organization_access = true;
    return;
   }
   $all_orgs_in_cls = "bu_org_id IN ('" . implode("','", array_keys($_SESSION['user_org_access'])) . "')  ||  bu_org_id IS NULL ";
  } else if (property_exists($$class, 'org_id') && in_array('org_id', $$class->field_a)) {
   if (empty($_SESSION['user_org_access'])) {
    $no_organization_access = true;
    return;
   }
   $all_orgs_in_cls = "org_id IN ('" . implode("','", array_keys($_SESSION['user_org_access'])) . "') ||  org_id IS NULL ";
  }

  if (count($whereFields) > 0) {
   $whereClause = " WHERE " . implode(" AND ", $whereFields);
   $count_sql_all_records = "SELECT COUNT(*) FROM " . $table_name . $whereClause;
   if (!empty($all_orgs_in_cls)) {
    $whereClause .= ' AND ' . $all_orgs_in_cls;
   }
   // And then create the SQL query itself.
   $sql = "SELECT * FROM " . $table_name . $whereClause;
   $count_sql = "SELECT COUNT(*) FROM " . $table_name . $whereClause;
   $all_download_sql = "SELECT * FROM  " . $table_name . $whereClause;
  } else {
   $count_sql_all_records = "SELECT COUNT(*) FROM " . $table_name;
   if (!empty($all_orgs_in_cls)) {
    $whereClause = ' WHERE ' . $all_orgs_in_cls;
   } else {
    $whereClause = null;
   }
   $sql = "SELECT * FROM " . $table_name . $whereClause;
   $count_sql = "SELECT COUNT(*) FROM " . $table_name . $whereClause;
   $all_download_sql = "SELECT * FROM  " . $table_name . $whereClause;
  }

  if (!empty($_GET['group_by'][0])) {
   $sum_element = $$class->search_groupBy_sum;
   $fetch_as = 'sum_' . $sum_element;
   $sql = "SELECT * , SUM($sum_element) as $fetch_as FROM " . $table_name . $whereClause;
   $sql .= " GROUP BY " . $_GET['group_by'][0];
   $count_sql .= " GROUP BY " . $_GET['group_by'][0];
   $all_download_sql = "SELECT  * , SUM($sum_element) FROM  " . $table_name . $whereClause;
   $all_download_sql .= " GROUP BY " . $_GET['group_by'][0];
  }

  if ((!empty($search_order_by)) && (!empty($search_asc_desc))) {
   if (is_array($search_order_by)) {
    $sql .= ' ORDER BY ';
    $all_download_sql .= ' ORDER BY ';
    foreach ($search_order_by as $key_oby => $value_oby) {
     if (empty($search_asc_desc[$key_oby])) {
      $search_asc_desc[$key_oby] = ' DESC ';
     }
     $sql .= $value_oby . ' ' . $search_asc_desc[$key_oby] . ' ,';
     $all_download_sql .= $value_oby . ' ' . $search_asc_desc[$key_oby] . ' ,';
    }
    $sql = rtrim($sql, ',');
    $all_download_sql = rtrim($all_download_sql, ',');
   } else {
    $sql .= ' ORDER BY ' . $search_order_by . ' ' . $search_asc_desc;
    $all_download_sql .= ' ORDER BY ' . $search_order_by . ' ' . $search_asc_desc;
   }
  }

  $total_count = $class::count_all_by_sql($count_sql);
  $total_count_all = $class::count_all_by_sql($count_sql_all_records);

  if (!empty($per_page)) {
   $pagination = new pagination($pageno, $per_page, $total_count);
   $pagination_statement = $pagination->show_pagination();
   $sql .=" LIMIT {$per_page} ";
   $sql .=" OFFSET {$pagination->offset()}";
  }
//  echo "<br><br><br> sql is $sql";
  $search_result = $class::find_by_sql($sql);
//  pa($search_result);
 }


 if (method_exists($class, 'search_add_extra_fields')) {
  $class::search_add_extra_fields($search_result);
 }

 if (property_exists($class, 'search')) {
  foreach ($$class->search as $searchParaKey => $searchParaValue) {
   $s->setProperty($searchParaKey, $searchParaValue);
  }
 }
 $total_count_all = empty($total_count_all) ? $total_count : $total_count_all;
 $s->setProperty('result', $search_result);
 $s->setProperty('_searching_class', $class);
 $s->setProperty('_per_page', $per_page);
 $s->setProperty('primary_column_s', $primary_column);
 $s->setProperty('column_array_s', $column_array);
 $search_result_statement = $search->search_result_op();


//check for schduling and schdule the report accordingly
 if (!empty($_GET['frequency_uom'][0]) && !empty($_GET['frequency_value'][0])) {
  $ps = new sys_program_schedule();
  $ps->program_class_name = $class;
  $program_name = !empty($function_name) ? $function_name : 'search_records';
  $ps->program_name = $program_name;
  $ps->frequency_uom = $_GET['frequency_uom'][0];
  $ps->frequency_value = $_GET['frequency_value'][0];
  $ps->start_date_time = $_GET['start_date_time'][0];
  $ps->op_email_address = $_GET['email_addresses'][0];
  $ps->op_email_format = $_GET['email_format'][0];
  $ps->status = 'ACTIVE';
  $ps->request_type = 'REPORT';
  $ps->parameter = serialize($postArray);
  $ps->report_query = base64_encode($all_download_sql);
  try {
   $ps->save();
   echo "<div class='message'>The program is Successfully schduled.sys_program_schedule_id id  " . $ps->sys_program_schedule_id . '</div>';
  } catch (Exception $e) {
   echo "<br>Unable to schedule the program. Error @ class_sys_program @@ line " . __LINE__ . '<br>' . $e->getMessage();
  }
 }


 $search_class_obj_all = $$class->findBySql($all_download_sql);
 $search_class_array_all = json_decode(json_encode($search_class_obj_all), true);

 if (!empty($_GET['email_addresses'])) {
  //send email
  $im = new inomail();
  $im->FromName = $si->site_name;
  $email_a = explode(',', $_GET['email_addresses'][0]);
  foreach ($email_a as $em_k => $email_v) {
   $im->addAddress($email_v);
  }
  $im->addReplyTo($si->email, 'Search Output');
  $im->Subject = 'Search Result';
  $im->Body = 'Please find attached the search result';

  switch ($_GET['email_format'][0]) {
   case 'text_format' :
    $report_op = array2text($search_class_array_all);
    $file_name = date("Y-m-d") . '_' . $class . '_report_output.txt';
    break;

   case 'pdf_format' :
    $report_op = array2pdf($search_class_array_all);
    $file_name = date("Y-m-d") . '_' . $class . '_report_output.pdf';
    break;

   case 'xml_format' :
    $report_op = array2xml($search_class_array_all);
    $file_name = date("Y-m-d") . '_' . $class . '_report_output.txt';
    break;

   case 'worddoc_format' :
    $report_op = array2worddoc($search_class_array_all);
    $file_name = date("Y-m-d") . '_' . $class . '_report_output.doc';
    break;

   default :
    $report_op = array2csv($search_class_array_all);
    $file_name = date("Y-m-d") . '_' . $class . '_report_output.csv';
    break;
  }

  $im->addStringAttachment($report_op, $file_name);
  $im->ino_sendMail();
 }

 include_once(__DIR__ . '/../template/json_search_template.inc');
 echo '</div>';

 $dbc->confirm();
}
?>