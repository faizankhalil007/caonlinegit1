<?php

class ModelAccountStatusTracker extends Model
{

    public $generation_list = array();
    public $ambassador_id = 0;
    public $generation_level = 0;
    public $generation_ids = '';
    public $data = array();
    public $total_of_sale_percentage = 0.8;
    public $total_of_personal_sale_percentage = 1;
    public $number_of_previous_months = 0;

    public function getAmbassadorBankDetails () {

        $sql_select = "SELECT bank_account_number, routing_number, bank_account_type, institutional_id_number, is_invalid_bank_details FROM " . DB_PREFIX . "ambassador WHERE customer_id = '".(int)$this->customer->getId()."' AND ambassador_id = '".(int)$this->customer->getAmbassadorId()."' ";

        $query = $this->db->query($sql_select);

        if($query->rows)
            return $query->row;
        else
            return false;
    }

    public function check_ambassador_personal_sale($ambassador_currency, $total_sale)
    {
        $sql_select = "SELECT group_sale_setting_id
                       FROM " . DB_PREFIX . "ambassadors_ranking_system_group_sales_setting
                       WHERE " . (int)$total_sale . " >= personal_retail_sale AND
                       currency = '" . $ambassador_currency . "'
                    ";

        $query = $this->db->query($sql_select);

        if ($query->rows) {
            return true;
        }

        return false;
    }


    public function get_generation_list($ambassador_id = array(), $ambassador_currency, $sales_month,$reset_gen_list = false)
    {

        $this->generation_level++;

        $sql_select = "SELECT ambassador_id as generation_list
                       FROM al_ambassador aa
                       WHERE manager_id_f IN (" . $ambassador_id . ")
                    ";
        if($reset_gen_list === true){
            $this->generation_list = array();
            $this->generation_level = 1;
        }

        $query = $this->db->query($sql_select);

        if ($query->rows) {

            $generation_level_array = array();

            foreach ($query->rows as $key => $value) {

                $generation_level_array[] = $value['generation_list'];
            }


            $this->generation_ids = implode(",", $generation_level_array);

            $total_sale = $this->get_generation_total_sale($ambassador_currency, true, $sales_month);

            $this->generation_list[] = array('level' => $this->generation_level, 'generation_list' => $this->generation_ids, 'total_sale' => $total_sale, 'total_generation_users' => sizeof($generation_level_array));

            $this->get_generation_list($this->generation_ids, $ambassador_currency, $sales_month);

        }

        return $this->generation_list;


    }


    public function get_ambassadors_generation_sales_list($ambassador_id = array(), $ambassador_currency, $sales_month, &$generation_list, $generation_level = 0)
    {

        $generation_level++;

        $sql_select = "SELECT ambassador_id as generation_list
                       FROM al_ambassador aa
                       WHERE manager_id_f IN (" . $ambassador_id . ")
                    ";

        $query = $this->db->query($sql_select);

        if ($query->rows) {

            $generation_level_array = array();

            foreach ($query->rows as $key => $value) {

                $generation_level_array[] = $value['generation_list'];
            }


            $generation_ids = implode(",", $generation_level_array);

            $total_sale = $this->get_ambassador_generation_total_sale( $generation_ids, $ambassador_currency, true, $sales_month);

            $generation_list[] = array('level' => $generation_level, 'generation_list' => $generation_ids, 'total_sale' => $total_sale, 'total_generation_users' => sizeof($generation_level_array));

            $this->get_ambassadors_generation_sales_list($generation_ids, $ambassador_currency, $sales_month, $generation_list, $generation_level);

        }

        return $generation_list;


    }

    public function get_generation_total_sale($ambassador_currency, $filter_by_current_month = true, $number_of_previous_months = 0)
    {

        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sales_total_field = 'o.sales_val_' . strtolower($ambassador_currency);

        $sql_select = "SELECT SUM($sales_total_field) AS total, o.currency_id, o.currency_code FROM " . DB_PREFIX . "order" . $filter_table_name . " o ";

        if ($this->generation_ids != '') {
            $sql_select .= " WHERE o.ambassador_id IN (" . $this->generation_ids . ") AND o.order_status_id IN (3,5) AND o.order_type < 10 ";
        }

        if ($filter_by_current_month) {

            if ($number_of_previous_months > 0) {
                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }


        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'month';
        }

        switch ($group) {
            case 'day';
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql_select .= " GROUP BY YEAR(o.date_added), WEEK(o.date_added)";
                break;
            case 'month':
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added)";
                break;
            case 'year':
                $sql_select .= " GROUP BY YEAR(o.date_added)";
                break;
            case 'other':
                $sql_select .= " GROUP BY o.currency_id, o.currency_code";
                break;
        }

        $sql_select .= " ORDER BY o.date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql_select .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql_select);

        $total_sale = 0;

        if ($query->rows) {
            $total_sale = round($query->row['total'], 2);
        }

        $refund_amount = $this->get_ambassador_refund_payments ($filter_by_current_month, $number_of_previous_months);
        $total_sale = ($total_sale + $refund_amount);

        return ($total_sale * $this->total_of_sale_percentage);

    }

    public function get_ambassador_refund_payments ($filter_by_current_month, $number_of_previous_months) {

        $sql_select = "SELECT SUM(act.amount) AS refund_amount FROM " . DB_PREFIX . "customer_transaction  act ";
        $sql_select .= "INNER JOIN " . DB_PREFIX . "ambassador aa ON aa.customer_id = act.customer_id";

        if ($this->generation_ids != '') {
            $sql_select .= " WHERE aa.ambassador_id IN (" . $this->generation_ids . ") ";
        }

        if ($filter_by_current_month) {
            if ($number_of_previous_months > 0) {
                $sql_select .= " AND (act.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {
                $sql_select .= " AND YEAR(act.date_added) = YEAR(CURRENT_DATE()) AND MONTH(act.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }

        $sql_select .= " ORDER BY act.date_added DESC";

        $query = $this->db->query($sql_select);

        $refund_amount = 0;

        if ($query->rows) {
            $refund_amount = $query->row['refund_amount'];
        }

        return $refund_amount;
    }


    public function get_ambassador_generation_total_sale( $generation_ids, $ambassador_currency, $filter_by_current_month = true, $number_of_previous_months = 0)
    {

        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sql_select = "SELECT SUM(ot.value) AS total, o.currency_id, o.currency_code FROM " . DB_PREFIX . "order" . $filter_table_name . " o ";
        $sql_select .= "INNER JOIN " . DB_PREFIX . "order_total" . $filter_table_name . " ot ON ot.order_id = o.order_id";

        if ($generation_ids != '') {
            $sql_select .= " WHERE o.ambassador_id IN (" . $generation_ids . ") AND o.order_status_id IN (3,5) AND o.order_type < 10 ";
        }

        if (!empty($ambassador_currency)) {
            $sql_select .= " AND ot.code = 'total_" . strtolower($ambassador_currency) . "' ";
        } else {
            $sql_select .= " AND ot.code = 'total' ";
        }


        if ($filter_by_current_month) {

            if ($number_of_previous_months > 0) {
                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }


        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'month';
        }

        switch ($group) {
            case 'day';
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql_select .= " GROUP BY YEAR(o.date_added), WEEK(o.date_added)";
                break;
            case 'month':
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added)";
                break;
            case 'year':
                $sql_select .= " GROUP BY YEAR(o.date_added)";
                break;
            case 'other':
                $sql_select .= " GROUP BY o.currency_id, o.currency_code";
                break;
        }

        $sql_select .= " ORDER BY o.date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql_select .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql_select);

        $total_sale = 0;

        if ($query->rows) {
            $total_sale = round($query->row['total'], 2);
        }

        $shipping_total = $this->get_ambassador_shipping_total($generation_ids, $ambassador_currency, $filter_by_current_month, $number_of_previous_months);

        $total_sale = $total_sale - $shipping_total;

        return ($total_sale * $this->total_of_sale_percentage);

    }

    function check_locked_down_orders_month ( $filter_date_month ) {

        $previous_month_year = date('Y-m', strtotime("-$filter_date_month month"));

        $sql_select = "SELECT * FROM " . DB_PREFIX . "orders_lock_down_log WHERE month_year = '" . $previous_month_year . "' ";

        $query = $this->db->query($sql_select);

        if ($query->row) {

            return true;
        }

        return false;

    }

    public function get_ambassador_total_sale($ambassador_id = 0, $ambassador_currency = 0, $filter_by_current_month = true, $number_of_previous_months, $data = array())
    {
        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $this->ambassador_id = $ambassador_id;
        $this->generation_ids = $ambassador_id;
        $this->number_of_previous_months = $number_of_previous_months;

        $sql_sub_query = '';

        if ($filter_by_current_month) {
            if ($number_of_previous_months > 0) {
                $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aa.customer_id AND (act.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) )  AS refund_amount ";
            } else {
                $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aa.customer_id AND YEAR(act.date_added) = YEAR(CURRENT_DATE()) AND MONTH(act.date_added) = MONTH(CURRENT_DATE()) )   AS refund_amount";
            }
        }

        $sales_total_field = 'o.sales_val_' . strtolower($ambassador_currency);


        $sql_select = "SELECT SUM($sales_total_field) AS total, o.currency_id, o.currency_code, " . $sql_sub_query . "
                        FROM " . DB_PREFIX . "order" . $filter_table_name . " o
                        INNER JOIN " . DB_PREFIX . "ambassador aa ON aa.ambassador_id = o.ambassador_id
                        ";

        if ($ambassador_id > 0) {
            $sql_select .= " WHERE o.ambassador_id ='" . $ambassador_id . "' AND o.order_status_id IN (3,5) AND o.order_type < 10 ";
        }

        if ($filter_by_current_month) {

            if ($number_of_previous_months > 0) {
                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'month';
        }

        switch ($group) {
            case 'day';
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql_select .= " GROUP BY YEAR(o.date_added), WEEK(o.date_added)";
                break;
            case 'month':
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added)";
                break;
            case 'year':
                $sql_select .= " GROUP BY YEAR(o.date_added)";
                break;
            case 'other':
                $sql_select .= " GROUP BY o.currency_id, o.currency_code";
                break;
        }

        $sql_select .= " ORDER BY o.date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql_select .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        //echo "<pre>"; print_r($sql_select); echo "</pre>"; //exit();

        $query = $this->db->query($sql_select);

        $total_sale = 0;
        $refund_amount = 0;

        if ($query->rows) {
            $total_sale = round($query->row['total'], 2);
            $refund_amount = round($query->row['refund_amount'], 2);
        }

       $total_sale = ($total_sale + $refund_amount);

        return ($total_sale * $this->total_of_personal_sale_percentage);
    }


    public function get_ambassador_1_14_sale($ambassador_id = 0, $ambassador_currency = 0, $filter_by_current_month = true, $number_of_previous_months, $data = array())
    {
        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $this->ambassador_id = $ambassador_id;
        $this->generation_ids = $ambassador_id;
        $this->number_of_previous_months = $number_of_previous_months;


        $sql_select = "SELECT SUM(ot.value) AS total, o.currency_id, o.currency_code FROM " . DB_PREFIX . "order" . $filter_table_name . " o ";
        $sql_select .= "INNER JOIN " . DB_PREFIX . "order_total" . $filter_table_name . " ot ON ot.order_id = o.order_id";

        if ($ambassador_id > 0) {
            $sql_select .= " WHERE o.ambassador_id ='" . $ambassador_id . "' AND o.order_status_id IN (3,5) AND o.order_type < 10 ";
        }

        if (!empty($ambassador_currency)) {
            $sql_select .= " AND ot.code = 'total_" . strtolower($ambassador_currency) . "' ";
        } else {
            $sql_select .= " AND ot.code = 'total' ";
        }


        if ($filter_by_current_month) {

            if ($number_of_previous_months > 0) {
                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $start_date = date('Y-m-01');
                $end_date = date('Y-m-14');

                $sql_select .= " AND DATE(o.date_added) BETWEEN DATE('" . $start_date . "') AND DATE('" . $end_date . "')";
            }
        }

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'month';
        }

        switch ($group) {
            case 'day';
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql_select .= " GROUP BY YEAR(o.date_added), WEEK(o.date_added)";
                break;
            case 'month':
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added)";
                break;
            case 'year':
                $sql_select .= " GROUP BY YEAR(o.date_added)";
                break;
            case 'other':
                $sql_select .= " GROUP BY o.currency_id, o.currency_code";
                break;
        }

        $sql_select .= " ORDER BY o.date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql_select .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        //echo "<pre>"; print_r($sql_select); echo "</pre>";

        $query = $this->db->query($sql_select);

        $total_sale = 0;

        if ($query->rows) {
            $total_sale = round($query->row['total'], 2);
        }

        $shipping_total = $this->get_shipping_total($ambassador_currency, $filter_by_current_month, $number_of_previous_months, $data);

        $total_sale = $total_sale - $shipping_total;

        return ($total_sale * $this->total_of_personal_sale_percentage);
    }

    function get_shipping_total($ambassador_currency, $filter_by_current_month = true, $number_of_previous_months, $data = array())
    {

        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sql_select = "SELECT SUM(ot.value) AS shipping_total FROM " . DB_PREFIX . "order" . $filter_table_name . " o ";
        $sql_select .= "INNER JOIN " . DB_PREFIX . "order_total" . $filter_table_name . " ot ON ot.order_id = o.order_id";

        if ($this->generation_ids != '') {
            $sql_select .= " WHERE o.ambassador_id IN (" . $this->generation_ids . ") AND o.order_status_id IN (3,5) AND o.order_type < 10 ";
        }

        $sql_select .= " AND ot.code = 'shipping' ";


        if ($filter_by_current_month) {


            if ($number_of_previous_months > 0) {

                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'month';
        }

        switch ($group) {
            case 'day';
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql_select .= " GROUP BY YEAR(o.date_added), WEEK(o.date_added)";
                break;
            case 'month':
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added)";
                break;
            case 'year':
                $sql_select .= " GROUP BY YEAR(o.date_added)";
                break;
            case 'other':
                $sql_select .= " GROUP BY o.currency_id, o.currency_code";
                break;
        }

        $sql_select .= " ORDER BY o.date_added DESC";

        $query = $this->db->query($sql_select);

        $total_shipping_cost = 0;

        if ($query->rows) {

            if (strtoupper($ambassador_currency) == "USD") {
                $total_shipping_cost = $query->row['shipping_total'];

            } elseif (strtoupper($ambassador_currency) == "GBP") {
                $total_shipping_cost = $this->currency->convert($query->row['shipping_total'], $this->config->get('config_currency'), 'GBP');
            } elseif (strtoupper($ambassador_currency) == "CAD") {
                $total_shipping_cost = $this->currency->convert($query->row['shipping_total'], $this->config->get('config_currency'), 'CAD');
            }
        }

        $total_shipping_cost = round($total_shipping_cost, 2, PHP_ROUND_HALF_UP);

        return $total_shipping_cost;


    }

    function get_tax_total($ambassador_currency, $filter_by_current_month = true, $number_of_previous_months, $data = array())
    {

        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sql_select = "SELECT SUM(ot.value) AS tax_total FROM " . DB_PREFIX . "order" . $filter_table_name . " o ";
        $sql_select .= "INNER JOIN " . DB_PREFIX . "order_total" . $filter_table_name . " ot ON ot.order_id = o.order_id";

        if ($this->generation_ids != '') {
            $sql_select .= " WHERE o.ambassador_id IN (" . $this->generation_ids . ") AND o.order_status_id IN (3,5) AND o.order_type < 10 ";
        }

        $sql_select .= " AND ot.code = 'tax' ";


        if ($filter_by_current_month) {


            if ($number_of_previous_months > 0) {

                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'month';
        }

        switch ($group) {
            case 'day';
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql_select .= " GROUP BY YEAR(o.date_added), WEEK(o.date_added)";
                break;
            case 'month':
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added)";
                break;
            case 'year':
                $sql_select .= " GROUP BY YEAR(o.date_added)";
                break;
            case 'other':
                $sql_select .= " GROUP BY o.currency_id, o.currency_code";
                break;
        }

        $sql_select .= " ORDER BY o.date_added DESC";

        $query = $this->db->query($sql_select);

        $total_tax_cost = 0;

        if ($query->rows) {
            if (strtoupper($ambassador_currency) == "USD") {
                $total_tax_cost = $query->row['tax_total'];

            } elseif (strtoupper($ambassador_currency) == "GBP") {
                $total_tax_cost = $this->currency->convert($query->row['tax_total'], $this->config->get('config_currency'), 'GBP');
            } elseif (strtoupper($ambassador_currency) == "CAD") {
                $total_tax_cost = $this->currency->convert($query->row['tax_total'], $this->config->get('config_currency'), 'CAD');
            }
        }

        $total_tax_cost = round($total_tax_cost, 2, PHP_ROUND_HALF_UP);

        return $total_tax_cost;

    }

    function get_ambassador_shipping_total($generation_ids, $ambassador_currency, $filter_by_current_month = true, $number_of_previous_months, $data = array())
    {

        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sql_select = "SELECT SUM(ot.value) AS shipping_total FROM " . DB_PREFIX . "order" . $filter_table_name . " o ";
        $sql_select .= "INNER JOIN " . DB_PREFIX . "order_total" . $filter_table_name . " ot ON ot.order_id = o.order_id";

        if ($generation_ids != '') {
            $sql_select .= " WHERE o.ambassador_id IN (" . $generation_ids . ") AND o.order_status_id IN (3,5) AND o.order_type < 10 ";
        }

        $sql_select .= " AND ot.code = 'shipping' ";


        if ($filter_by_current_month) {


            if ($number_of_previous_months > 0) {

                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }

        if (!empty($data['filter_group'])) {
            $group = $data['filter_group'];
        } else {
            $group = 'month';
        }

        switch ($group) {
            case 'day';
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added), DAY(o.date_added)";
                break;
            default:
            case 'week':
                $sql_select .= " GROUP BY YEAR(o.date_added), WEEK(o.date_added)";
                break;
            case 'month':
                $sql_select .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added)";
                break;
            case 'year':
                $sql_select .= " GROUP BY YEAR(o.date_added)";
                break;
            case 'other':
                $sql_select .= " GROUP BY o.currency_id, o.currency_code";
                break;
        }

        $sql_select .= " ORDER BY o.date_added DESC";

        $query = $this->db->query($sql_select);

        $total_shipping_cost = 0;

        if ($query->rows) {

            if (strtoupper($ambassador_currency) == "USD") {
                $total_shipping_cost = $query->row['shipping_total'];

            } elseif (strtoupper($ambassador_currency) == "GBP") {
                $total_shipping_cost = $this->currency->convert($query->row['shipping_total'], $this->config->get('config_currency'), 'GBP');
            } elseif (strtoupper($ambassador_currency) == "CAD") {
                $total_shipping_cost = $this->currency->convert($query->row['shipping_total'], $this->config->get('config_currency'), 'CAD');
            }
        }

        $total_shipping_cost = round($total_shipping_cost, 2);

        return $total_shipping_cost;


    }

    public function get_sales_list($ambassador_id, $ambassador_currency, $filter_by_current_month = true, $number_of_previous_months = 0)
    {
        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

       /* $sql_sub_query = '';

        if ($filter_by_current_month) {
            if ($number_of_previous_months > 0) {
                $sql_sub_query =  "(SELECT COALESCE(amount, 0) FROM al_customer_transaction act WHERE act.customer_id = a.customer_id AND (act.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) )  AS refund_amount ";
            } else {
                $sql_sub_query =  "(SELECT COALESCE(amount, 0) FROM al_customer_transaction act WHERE act.customer_id = a.customer_id AND YEAR(act.date_added) = YEAR(CURRENT_DATE()) AND MONTH(act.date_added) = MONTH(CURRENT_DATE()) )   AS refund_amount";
            }
        }*/

        $sales_total_field = 'o.sales_val_' . strtolower($ambassador_currency);

        $sql_select = "SELECT o.order_id, o.date_added, $sales_total_field as total, $sales_total_field * .8 AS wholesale, $sales_total_field * .2 AS tweenty_percent
                        FROM " . DB_PREFIX . "order" . $filter_table_name . " o
                       INNER JOIN " . DB_PREFIX . "ambassador a ON a.ambassador_id = o.ambassador_id
                       INNER JOIN " . DB_PREFIX . "customer c ON c.ambassador_id = a.ambassador_id
                       INNER JOIN " . DB_PREFIX . "address aa ON aa.customer_id = c.customer_id AND aa.address_id = c.address_id ";


        if ($ambassador_id != '') {
            $sql_select .= " WHERE o.ambassador_id IN (" . $ambassador_id . ")  AND o.order_type < 10 AND o.order_status_id IN (3,5) ";
        }

        if ($filter_by_current_month) {

            if ($number_of_previous_months > 0) {
                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }
        $sql_select .= " ORDER BY o.date_added ASC";

        $query = $this->db->query($sql_select);

        return $query->rows;

    }

    public function get_generations_sale_list($ambassador_id, $ambassador_currency, $filter_by_current_month = true, $number_of_previous_months = 0)
    {

        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sql_sub_query = '';

        if ($filter_by_current_month) {
            if ( $number_of_previous_months > 0 ) {
                $sql_sub_query = ", (SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = a.customer_id AND (act.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) )  AS refund_amount ";
            } else {
                $sql_sub_query = ", (SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = a.customer_id AND YEAR(act.date_added) = YEAR(CURRENT_DATE()) AND MONTH(act.date_added) = MONTH(CURRENT_DATE()) )   AS refund_amount";
            }
        }

        $sales_total_field = 'o.sales_val_' . strtolower($ambassador_currency);

        $sql_select = "SELECT o.order_id, o.date_added, sum($sales_total_field) as gross_sale, o.ambassador_id, a.full_name AS name
                        ".$sql_sub_query."
                        FROM " . DB_PREFIX . "order" . $filter_table_name . " o
                       INNER JOIN " . DB_PREFIX . "ambassador a ON a.ambassador_id = o.ambassador_id
                       INNER JOIN " . DB_PREFIX . "customer c ON c.ambassador_id = a.ambassador_id
                       INNER JOIN " . DB_PREFIX . "address aa ON aa.customer_id = c.customer_id AND aa.address_id = c.address_id ";

        if ($ambassador_id != '') {
            $sql_select .= " WHERE o.ambassador_id IN (" . $ambassador_id . ")  AND o.order_type < 10 AND o.order_status_id IN (3,5) ";
        }

        if ($filter_by_current_month) {

            if ($number_of_previous_months > 0) {
                $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
            } else {

                $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
            }
        }

        $sql_select .= " GROUP BY a.ambassador_id";
        $sql_select .= " ORDER BY gross_sale DESC";

        $query = $this->db->query($sql_select);

        return $query->rows;

    }


    public function get_ambassador_ranking_system_group_sale_settings($ambassador_currency)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "ambassadors_ranking_system_group_sales_setting";

        $sql .= " WHERE group_level IS NOT NULL AND currency = '" . $ambassador_currency . "' ";

        $implode = array();


        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $implode[] = "status = '" . (int)$data['filter_status'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if ($implode) {
            $sql .= " AND " . implode(" AND ", $implode);
        }

        $sort_data = array(
            'group_level',
            'personal_retail_sale',
            'generation_1_sale',
            'generation_2_sale',
            'generation_3_sale',
            'currency',
            'type',
            'generation_1_percentage',
            'generation_2_percentage',
            'generation_3_percentage',
            'generation_4_percentage',
            'generation_5_percentage',
            'generation_6_percentage',
            'status',
            'date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY group_level";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }


        $query = $this->db->query($sql);

        return $query->rows;

    }


    public function calculate_acti_cash_credit($ambassador_id = 0, $ambassador_currency = 'USD', $number_of_previous_months = 0, $acti_cash_type = 0)
    {
        //$ambassador_id = 19065;

        $sql_select = "SELECT SUM(credit) AS total
                        FROM " . DB_PREFIX . "ambassador_acti_cash_credit
                        WHERE  ambassador_id ='" . $ambassador_id . "' AND
                        currency = '" . $ambassador_currency . "' AND
                        credit >= 0 AND type != 5
                    ";

        if ($acti_cash_type > 0) {
            $sql_select .= " AND type = $acti_cash_type ";
        }

        if ($number_of_previous_months > 0) {
            $sql_select .= " AND (date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . (int)$number_of_previous_months . " MONTH, '%Y-%m-07 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
        } else {

            $sql_select .= " AND YEAR(date_added) = YEAR(CURRENT_DATE()) AND MONTH(date_added) = MONTH(CURRENT_DATE()) ";
        }

        $query = $this->db->query($sql_select);

        $total_sale = 0;

        if ($query->rows) {
            $total_sale = round($query->row['total'], 2);
        }

        return $total_sale;
    }


    public function calculate_ambassador_personal_sale_bonus($ambassador_currency, $ambassador_total_sale)
    {
        //$ambassador_total_sale = 701;

        $sql = "SELECT percentage
                FROM " . DB_PREFIX . "ambassadors_ranking_system_personal_sales_setting
                WHERE (
                        $ambassador_total_sale >= minimum_personal_sale AND
                        $ambassador_total_sale <= maximum_personal_sale) AND
                        currency = '" . $ambassador_currency . "'
                ";

        $query = $this->db->query($sql);

        if ($query->row) {
            return $query->row;
        } else
            return false;

    }


     public function calculate_gift_card_total($ambassador_id, $ambassador_currency, $number_of_previous_months = 0)
    {
        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sql_select = "SELECT sum(ot.value) as gift_card_total FROM " . DB_PREFIX . "order" . $filter_table_name . " o
                       INNER JOIN " . DB_PREFIX . "order_total" . $filter_table_name . " ot ON ot.order_id = o.order_id AND o.order_status_id IN (3,5)
                       INNER JOIN " . DB_PREFIX . "ambassador a ON a.ambassador_id = o.ambassador_id ";

        $sql_select .= " WHERE o.customer_id =  '" . $this->customer->getID() . "' AND o.ambassador_id = '" . $ambassador_id . "' ";
        $sql_select .= " AND ot.code = 'giftteaser' AND ot.value > 0 ";

        if ($number_of_previous_months > 0) {
            $sql_select .= " AND (o.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
        } else {

            $sql_select .= " AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";
        }

        $query = $this->db->query($sql_select);

        $total_giftcard_value = 0;

        if ($query->rows) {

            if (strtoupper($ambassador_currency) == "USD") {
                $total_giftcard_value = $query->row['gift_card_total'];

            } elseif (strtoupper($ambassador_currency) == "GBP") {
                $total_giftcard_value = $this->currency->convert($query->row['gift_card_total'], $this->config->get('config_currency'), 'GBP');
            } elseif (strtoupper($ambassador_currency) == "CAD") {
                $total_giftcard_value = $this->currency->convert($query->row['gift_card_total'], $this->config->get('config_currency'), 'CAD');
            }
        }

        return $total_giftcard_value;

    }


    function get_ambassadors_sales_list_by_ambassador_level ( $ambassador_id = 0, $ambassador_currency = 'USD', $number_of_previous_months = 0, $filter_ambassadors_from_sale = false ) {
        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $filter_ambassadors_from_sale_sql = '';

        if ( $filter_ambassadors_from_sale )
            $filter_ambassadors_from_sale_sql = ' AND ambassador_id NOT IN ( ' . $filter_ambassadors_from_sale . ')';

        $sql_sub_query = '';

        if ($number_of_previous_months > 0) {
            $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aar.customer_id AND (act.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) )  AS refund_amount ";
        } else {
            $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aar.customer_id AND YEAR(act.date_added) = YEAR(CURRENT_DATE()) AND MONTH(act.date_added) = MONTH(CURRENT_DATE()) )   AS refund_amount";
        }

        $sales_total_field = 'ao.sales_val_' . strtolower($ambassador_currency);


        $sql = "WITH RECURSIVE al_ambassador_recursive (ambassador_id, customer_id, full_name, manager_name, manager_id_f, ambassador_group, amb_level) AS
                    (
                      SELECT ambassador_id, customer_id, full_name, manager_name, manager_id_f, ambassador_id as ambassador_group, 1 level
                        FROM " . DB_PREFIX . "ambassador
                        WHERE manager_id_f = '" . $ambassador_id . "' " . $filter_ambassadors_from_sale_sql . "
                      UNION ALL
                      SELECT c.ambassador_id, c.customer_id, c.full_name, cp.manager_name, c.manager_id_f, CONCAT(cp.ambassador_group, ',', c.ambassador_id), cp.amb_level +1
                        FROM " . DB_PREFIX . "ambassador_recursive AS cp
                        JOIN " . DB_PREFIX . "ambassador AS c  ON cp.ambassador_id = c.manager_id_f
                    )
                    SELECT  aar.amb_level, sum($sales_total_field) as gross_sale,
                     " . $sql_sub_query . "
                    FROM al_ambassador_recursive aar
                    INNER JOIN " . DB_PREFIX . "order" . $filter_table_name . " ao ON ao.ambassador_id = aar.ambassador_id
                    WHERE ao.order_status_id IN (3,5) AND ao.order_type < 10
                ";

        if ($number_of_previous_months > 0) {
            $sql .= " AND (ao.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
        } else {

            $sql .= " AND YEAR(ao.date_added) = YEAR(CURRENT_DATE()) AND MONTH(ao.date_added) = MONTH(CURRENT_DATE()) ";
        }

        $sql .= " GROUP BY aar.amb_level ";
        $sql .= " ORDER BY aar.amb_level";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function ambassador_rank_bonus($ambassador_id, $number_of_previous_months = 0) {
        $sql_select = "SELECT * FROM " . DB_PREFIX . "ambassador_monthly_rank amr
                       WHERE amr.ambassador_id  = '".$ambassador_id."' AND amr.year > 2019
                       ORDER BY amr.year desc, amr.month DESC
                       LIMIT " . $number_of_previous_months . ", 3
                       ";

        $query = $this->db->query($sql_select);

        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    public function ambassador_average_rank_bonus ($ambassador_id) {
        $sql_select = "SELECT *, CONCAT_WS('-', YEAR, MONTH) as rank_achieved_month_year, DATE_ADD(CONCAT_WS('-', YEAR, MONTH, '01'), INTERVAL 12 MONTH) AS payable_date FROM " . DB_PREFIX . "ambassador_monthly_rank
                       WHERE ambassador_id  = '".$ambassador_id."' AND rank >=5
                       ORDER BY month, year
                       LIMIT 0, 1
                       ";

        $query = $this->db->query($sql_select);

        if ($query->num_rows) {
            return $query->row;
        }

        return false;
    }

    public function getAmbassadorsYearlyRankBonus($ambassador_id = 0, $currency = 'USD', $start_date, $end_date) {

        $start_date_month_year = explode ('-', $start_date);
        $end_date_month_year = explode ('-', $end_date);

        $start_date_year = $start_date_month_year[0];
        $start_date_month = $start_date_month_year[1];

        $end_date_year = $end_date_month_year[0];
        $end_date_month = $end_date_month_year[1];


        $sql_select = "SELECT FLOOR(AVG(rank)) as avg_rank
                            FROM al_ambassador_monthly_rank aamr
                            WHERE aamr.ambassador_id = '" . $ambassador_id . "'
                              AND ( MONTH BETWEEN '" . $start_date_month . "' AND '" . $end_date_month . "')
                              AND ( YEAR BETWEEN '" . $start_date_year . "' AND '" . $end_date_year . "')
                              ";
        $sql_select .= " HAVING avg_rank >= 5";

        $query = $this->db->query($sql_select);

        if( $query->num_rows ) {

            $response = $this->getAmbassadorTotalBonus ( $query->row['avg_rank'], $currency);

            if ( $response ) {
                $json_rank_bonus_array = array ( 'avg_rank' => $query->row['avg_rank'], 'bonus' => $response);

                return json_encode ($json_rank_bonus_array);
            }

            return false;
        }

        return false;
    }

    public function getAmbassadorTotalBonus ( $rank, $currency ) {

        $sql_select = "SELECT bonus
                           FROM " . DB_PREFIX . "ambassador_rank_bonus_settings
                           WHERE rank ='" . $rank . "' AND   currency = '".$currency."'
                           ";
        $query = $this->db->query($sql_select);

        if( $query->num_rows ) {
            return $query->row['bonus'];
        }

        return false;

    }

    public function get_active_loyalty_members_count ($ambassador_id, $number_of_previous_months = 0) {

        $sql_select = "SELECT as1.country_iso_code, as1.subscription_value, DATE_FORMAT(as1.date_added,'%Y-%m') as filter_date
                        FROM al_subscription as1
                        INNER JOIN al_customer ac ON ac.customer_id = as1.customer_id
                        INNER JOIN al_customer_shop_lock acsl ON acsl.customer_id = as1.customer_id
                        WHERE acsl.ambassador_id = '".$ambassador_id."'  AND as1.is_active = 1
                       ";

        $filter_year_month = date('Y-m', strtotime(date('Y-m-01')."-$number_of_previous_months month"));
        $sql_select .= "HAVING '" . $filter_year_month . "' >= filter_date";

        $query = $this->db->query($sql_select);

        if ($query->num_rows) {
            return $query->rows;
        }

        return 0;
    }

    public function get_ambassador_ranking_bonus_settings ($ambassador_currency, $rank) {

        $sql_select = "SELECT bonus FROM " . DB_PREFIX . "ambassador_rank_bonus_settings
                       WHERE rank  = '" . $rank . "' AND currency = '" . $ambassador_currency . "'
                       LIMIT 0, 1
                       ";

        $query = $this->db->query($sql_select);

        if ($query->num_rows) {
            return $query->row['bonus'];
        }

        return false;
    }


    function get_ambassador_overall_generation_sales ( $ambassador_id = 0, $ambassador_currency = 'USD', $number_of_previous_months = 0 ) {

        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sql_sub_query = '';

        if ($number_of_previous_months > 0) {
            $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aar.customer_id AND (act.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) )  AS refund_amount ";
        } else {
            $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aar.customer_id AND YEAR(act.date_added) = YEAR(CURRENT_DATE()) AND MONTH(act.date_added) = MONTH(CURRENT_DATE()) )   AS refund_amount";
        }

        $sales_total_field = 'ao.sales_val_' . strtolower($ambassador_currency);

        $sql = "WITH RECURSIVE al_ambassador_recursive (ambassador_id, customer_id, full_name, manager_name, manager_id_f, ambassador_group, amb_level) AS
                    (
                      SELECT ambassador_id, customer_id, full_name, manager_name, manager_id_f, ambassador_id as ambassador_group, 1 level
                        FROM " . DB_PREFIX . "ambassador
                        WHERE manager_id_f = '" . $ambassador_id . "'
                      UNION ALL
                      SELECT c.ambassador_id, c.customer_id, c.full_name, cp.manager_name, c.manager_id_f, CONCAT(cp.ambassador_group, ',', c.ambassador_id), cp.amb_level +1
                        FROM " . DB_PREFIX . "ambassador_recursive AS cp
                        JOIN " . DB_PREFIX . "ambassador AS c  ON cp.ambassador_id = c.manager_id_f
                    )
                    SELECT sum($sales_total_field) as gross_sale,
                    ".$sql_sub_query."
                    FROM al_ambassador_recursive aar
                    INNER JOIN al_order" .$filter_table_name. " ao ON ao.ambassador_id = aar.ambassador_id
                    WHERE ao.order_status_id IN (3,5) AND ao.order_type < 10
                ";

        if ($number_of_previous_months > 0) {
            $sql .= " AND (ao.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
        } else {

            $sql .= " AND YEAR(ao.date_added) = YEAR(CURRENT_DATE()) AND MONTH(ao.date_added) = MONTH(CURRENT_DATE()) ";
        }
        $query = $this->db->query($sql);

        $total = 0;

        if ($query->rows) {

            $total = (( $query->row['gross_sale'] + $query->row['refund_amount']) * 0.8 );

        }

        return $total;
    }


    function get_ambasassadors_having_sale_over_fourty_percentage ( $ambassador_id = 0, $ambassador_currency = 'USD', $number_of_previous_months = 0, $total_sale ) {

        $locked_down_status =  $this->check_locked_down_orders_month($number_of_previous_months);

        $filter_table_name = '';

        if( $locked_down_status )
            $filter_table_name = '_lock_down';

        $sql_sub_query = '';

        if ($number_of_previous_months > 0) {
            $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aa.customer_id AND (act.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) )  AS refund_amount ";
        } else {
            $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aa.customer_id AND YEAR(act.date_added) = YEAR(CURRENT_DATE()) AND MONTH(act.date_added) = MONTH(CURRENT_DATE()) )   AS refund_amount";
        }

        $sales_total_field = 'ao.sales_val_' . strtolower($ambassador_currency);

        $sql = "WITH RECURSIVE al_ambassador_recursive (ambassador_id, customer_id, full_name, manager_name, manager_id_f, ambassador_group, amb_level) AS
                    (
                      SELECT ambassador_id, customer_id, full_name, manager_name, manager_id_f, ambassador_id as ambassador_group, 1 level
                        FROM " . DB_PREFIX . "ambassador
                        WHERE manager_id_f = '" . $ambassador_id . "'
                      UNION ALL
                      SELECT c.ambassador_id, c.customer_id, c.full_name, cp.manager_name, c.manager_id_f, CONCAT(cp.ambassador_group, ',', c.ambassador_id), cp.amb_level +1
                        FROM " . DB_PREFIX . "ambassador_recursive AS cp
                        JOIN " . DB_PREFIX . "ambassador AS c  ON cp.ambassador_id = c.manager_id_f
                    )
                    SELECT aar.ambassador_id, aar.manager_id_f,  aar.amb_level, (sum($sales_total_field)*0.8) as gross_sale, ((sum($sales_total_field)*0.8) / $total_sale) * 100 as percentage
                    FROM al_ambassador_recursive aar
                    INNER JOIN " . DB_PREFIX . "order" .$filter_table_name. " ao ON ao.ambassador_id = aar.ambassador_id
                    WHERE ao.order_status_id IN (3,5) AND ao.order_type < 10
                ";


        if ($number_of_previous_months > 0) {
            $sql .= " AND (ao.date_added BETWEEN DATE_FORMAT(NOW() - INTERVAL " . $number_of_previous_months . " MONTH, '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(NOW() - INTERVAL " . $number_of_previous_months . " MONTH), '%Y-%m-%d 23:59:59')) ";
        } else {
            $sql .= " AND YEAR(ao.date_added) = YEAR(CURRENT_DATE()) AND MONTH(ao.date_added) = MONTH(CURRENT_DATE()) ";
        }

        $sql .= " GROUP BY aar.ambassador_group ";
        $sql .= " HAVING (gross_sale / $total_sale) > .4 ";
        $sql .= " ORDER BY aar.amb_level";

        $query = $this->db->query($sql);

        $ids = false;

        if($query->rows){

            $ambassador_id_array = array_column($query->rows, 'ambassador_id');
            $ids = implode(",", $ambassador_id_array);

            return $ids;
        }

        return $ids;
    }

    function get_ambassador_my_learning_locked_down_data ($ambassador_id, $filter_date_month ) {

        $previous_month_year = date('Y-m', strtotime("-$filter_date_month month"));

        $sql_select = "SELECT * FROM " . DB_PREFIX . "ambassador_my_earnings_summary
                        WHERE ambassador_id = '".$ambassador_id."' AND month_year = '" . $previous_month_year . "' ";

        $query = $this->db->query($sql_select);

        if ($query->row) {
            return $query->row;
        }

        return false;

    }

    function get_ambassador_sales_complete_locked_down_data ($ambassador_id, $filter_date_month ) {

        $previous_month_year = date('Y-m', strtotime("-$filter_date_month month"));

        $sql_select = "SELECT * FROM " . DB_PREFIX . "ambassador_sales_complete
                        WHERE ambassador_id = '".$ambassador_id."' AND month_year = '" . $previous_month_year . "' ";

        $query = $this->db->query($sql_select);

        if ($query->row) {
            return $query->row;
        }

        return false;

    }

    public function get_monthly_active_loyalty_members ($ambassador_id) {

        $sql_select = "SELECT as1.subscription_id, acsl.ambassador_id, as1.country_iso_code, as1.subscription_value, DATE_FORMAT(as1.date_added,'%Y-%m-%d') AS filter_date, concat_ws( ' ', ac.firstname, ac.lastname) as customer_name, as1.is_active as status, as1.customer_id,
                        aa.full_name as name,
                        CASE
                              WHEN a.country_id = 38 THEN 'CA'
                              WHEN a.country_id = 222 THEN 'GB'
                              WHEN a.country_id = 223 THEN 'US'
                              WHEN a.country_id = 81 THEN 'US'
                              WHEN a.country_id = 103 THEN 'GB'
                              ELSE 'None'
                          END AS 'country',
                          CASE
                              WHEN a.country_id = 38 THEN 'CAD'
                              WHEN a.country_id = 222 THEN 'GBP'
                              WHEN a.country_id = 223 THEN 'USD'
                              WHEN a.country_id = 81 THEN 'USD'
                              WHEN a.country_id = 103 THEN 'GBP'
                              ELSE 'None'
                          END AS 'currency',
                        aa.bank_account_number, aa.routing_number as sort_code, aa.bank_account_type, aa.institutional_id_number
                        FROM " . DB_PREFIX . "subscription as1
                        INNER JOIN " . DB_PREFIX . "customer ac ON ac.customer_id = as1.customer_id
                        INNER JOIN " . DB_PREFIX . "customer_shop_lock acsl ON acsl.customer_id = as1.customer_id
                        LEFT JOIN  " . DB_PREFIX . "ambassador aa ON aa.ambassador_id = acsl.ambassador_id AND aa.customer_id = ac.customer_id
                        LEFT JOIN  " . DB_PREFIX . "address a ON a.address_id = ac.address_id AND a.customer_id = ac.customer_id
                        WHERE acsl.ambassador_id = '".$ambassador_id."'
                        ";
//as1.is_active = 1 and

        //echo $sql_select;  exit();

        $query = $this->db->query($sql_select);

        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    public function get_vip_customers ($ambassador_id) {

        $sql_select = "SELECT ac.customer_id, CONCAT_WS (' ', ac.firstname, ac.lastname) AS name, ac.date_added
                       FROM al_customer ac
                       INNER JOIN al_customer_shop_lock acsl ON ac.customer_id = acsl.customer_id
                        WHERE ac.customer_group_id = 3 AND acsl.ambassador_id = '".$ambassador_id."'
                      ";


        $query = $this->db->query($sql_select);

        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    public function get_customer_last_order ( $customer_id ) {

        $sql_select = "SELECT o.order_id, o.date_added,os.name AS order_status 
                        FROM al_order o LEFT JOIN al_order_status os ON o.order_status_id = os.order_status_id
                        WHERE o.customer_id ='".$customer_id."' AND o.customer_group_id = 3
                        ORDER BY o.order_id DESC
                        LIMIT 0,1
                        ";

        $query = $this->db->query($sql_select);

        if ($query->num_rows) {
            $record = $query->row;
            $products =  $this->getOrderProductsData( $record['order_id'] );
            $record['products'] = $products;

            return $record;

        }

        return false;
    }

    public function getOrderProductsData($order_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

        return $query->rows;
    }

    public function checkMidMonthActiCashValidity($currency) {

        if (!empty($currency)) {
            $valu = "sales_val_".strtolower($currency);
        } else {
            $valu = "sales_val_gbp";
        }

        $sql_select = "SELECT SUM(o.{$valu}) AS total FROM " . DB_PREFIX . "order o ";
        $sql_select .= " WHERE o.ambassador_id = '".(int)$this->customer->getAmbassadorId()."' AND o.order_status_id IN (3,5) AND o.order_type < 10 ";
        $sql_select .= " AND DAY(o.date_added) <= 14 AND YEAR(o.date_added) = YEAR(CURRENT_DATE()) AND MONTH(o.date_added) = MONTH(CURRENT_DATE()) ";

        $query = $this->db->query($sql_select);

        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }


    public function get_ambassadors_monthly_sales() {

        $sql_sub_query =  "(SELECT COALESCE(SUM(amount), 0) FROM al_customer_transaction act WHERE act.customer_id = aa.customer_id AND YEAR(act.date_added) = YEAR(CURRENT_DATE()) AND MONTH(act.date_added) = MONTH(CURRENT_DATE()) )   AS refund_amount, ";

        $sql_select = "SELECT
                        (SUM(ot.value)) as gross_sale,
                        ((SUM(ot.value)) * 0.8) AS whole_sale,
                        " . $sql_sub_query . "
                        aa.full_name as name,
                        CASE
                            WHEN a.country_id = 38 THEN 'CA'
                            WHEN a.country_id = 222 THEN 'GB'
                            WHEN a.country_id = 223 THEN 'US'
                            WHEN a.country_id = 81 THEN 'US'
                            WHEN a.country_id = 103 THEN 'GB'
                            ELSE 'None'
                        END AS 'country',
                        CASE
                            WHEN a.country_id = 38 THEN 'CAD'
                            WHEN a.country_id = 222 THEN 'GBP'
                            WHEN a.country_id = 223 THEN 'USD'
                            WHEN a.country_id = 81 THEN 'USD'
                            WHEN a.country_id = 103 THEN 'GBP'
                            ELSE 'None'
                        END AS 'currency',
                        o.ambassador_id, aa.bank_account_number, aa.routing_number as sort_code, aa.bank_account_type, aa.institutional_id_number
                       FROM " . DB_PREFIX . "order o
                       INNER JOIN " . DB_PREFIX . "order_total ot ON ot.order_id = o.order_id AND o.ambassador_id != 1 AND  order_status_id IN (3,5)
                       INNER JOIN  " . DB_PREFIX . "ambassador aa ON aa.ambassador_id = o.ambassador_id
                       INNER JOIN  " . DB_PREFIX . "customer c ON c.ambassador_id = o.ambassador_id
                       INNER JOIN  " . DB_PREFIX . "address a ON a.customer_id = c.customer_id AND a.address_id = c.address_id
                       WHERE o.order_type < 10 AND (
                        CASE
                            WHEN a.country_id = 38 THEN ot.code = 'total_cad'
                            WHEN a.country_id = 222 THEN ot.code = 'total_gbp'
                            WHEN a.country_id = 223 THEN ot.code = 'total_usd'
                            WHEN a.country_id = 81 THEN ot.code = 'total_usd'
                            WHEN a.country_id = 103 THEN ot.code = 'total_gbp'
                            ELSE ot.code = 'total'
                        END )
                    ";

            $sql_select .= " AND o.date_added >= '".date('Y-m-01 00:00:00')."'";

        $sql_select .= " GROUP BY o.ambassador_id";
        $sql_select .= " HAVING gross_sale > 0";
        $sql_select .= " ORDER BY aa.ambassador_id ASC";

        $query = $this->db->query($sql_select);

        return $query->rows;
    }


    public function getPreviousHighestAmbassadorLevel($ambassador_id) {
        $sql = "SELECT MAX(rank) as max_rank FROM " . DB_PREFIX . "ambassador_monthly_rank WHERE ambassador_id ='".(int)$ambassador_id."'";
        $query = $this->db->query($sql);

        return $query->row['max_rank'];
    }

    public function checkTempAmbassadorLevel($ambassador_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "ambassador_temp_rank WHERE ambassador_id ='".(int)$ambassador_id."'";
        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->row;
        }

        return false;
    }

    public function addTempAmbassadorLevel($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "ambassador_temp_rank SET ambassador_id ='".(int)$data['ambassador_id']."', rank ='".(int)$data['rank']."' ON DUPLICATE KEY UPDATE rank = '".(int)$data['rank']."'");
    }

    public function getTotalVIPCustomerFriends ($vipCustomerId) {

        $sql = "
            SELECT COUNT(*) AS total
            FROM al_refer_to_friend rtf
            WHERE rtf.is_registered = 1 AND rtf.customer_id = $vipCustomerId
        ";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->rows[0]['total'];
        }

        return 0;
    }

    public function getVIPCustomerRewardPoints ($vipCustomerId) {

        $sql = "
            SELECT IFNULL(SUM(cr.points), 0) AS reward_points
            FROM al_customer_reward cr
            WHERE cr.customer_id = $vipCustomerId
        ";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->rows[0]['reward_points'];
        }

        return 0;
    }

    public function getGBFastStartAmbassadors() {
        $gbp_sql = "SELECT ac.customer_id, aa1.ambassador_id, aa1.manager_id_f, ac.date_added as joining_date from al_order ao
      INNER JOIN al_ambassador aa1 ON aa1.ambassador_id = ao.ambassador_id
      INNER JOIN al_customer ac ON ac.ambassador_id = aa1.ambassador_id
      INNER JOIN al_address aa ON aa.customer_id = ac.customer_id
      WHERE ((DATE(ac.date_added) > '2019-12-31' AND DATEDIFF(CURDATE(), DATE(ac.date_added)) < 30) OR (DATE(ac.date_added) < '2020-01-01' AND DATE(ao.date_added) > '2019-12-31'))
      AND (aa.country_id = 222 OR aa.country_id = 103) AND ao.order_type < '10' AND ao.order_status_id IN (3, 5)
      AND aa1.ambassador_id != 1 GROUP BY YEAR(ao.date_added), MONTH(ao.date_added), aa1.ambassador_id
      HAVING SUM(ao.sales_val_gbp) >= 400 ORDER BY SUM(ao.sales_val_gbp) DESC";

        $query = $this->db->query($gbp_sql);
        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    public function getUSFastStartAmbassadors() {
        $usd_sql = "SELECT ac.customer_id, aa1.ambassador_id, aa1.manager_id_f, ac.date_added as joining_date from al_order ao
      INNER JOIN al_ambassador aa1 ON aa1.ambassador_id = ao.ambassador_id
      INNER JOIN al_customer ac ON ac.ambassador_id = aa1.ambassador_id
      INNER JOIN al_address aa ON aa.customer_id = ac.customer_id
      WHERE ((DATE(ac.date_added) > '2019-12-31' AND DATEDIFF(CURDATE(), DATE(ac.date_added)) < 30) OR (DATE(ac.date_added) < '2020-01-01' AND DATE(ao.date_added) > '2019-12-31'))
      AND (aa.country_id = 223 OR aa.country_id = 81) AND ao.order_type < '10' AND ao.order_status_id IN (3, 5)
      AND aa1.ambassador_id != 1 GROUP BY YEAR(ao.date_added), MONTH(ao.date_added), aa1.ambassador_id
      HAVING SUM(ao.sales_val_usd) >= 500 ORDER BY SUM(ao.sales_val_usd) DESC";

        $query = $this->db->query($usd_sql);
        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

    public function getCAFastStartAmbassadors() {
        $cad_sql = "SELECT ac.customer_id, aa1.ambassador_id, aa1.manager_id_f, ac.date_added as joining_date from al_order ao
      INNER JOIN al_ambassador aa1 ON aa1.ambassador_id = ao.ambassador_id
      INNER JOIN al_customer ac ON ac.ambassador_id = aa1.ambassador_id
      INNER JOIN al_address aa ON aa.customer_id = ac.customer_id
      WHERE ((DATE(ac.date_added) > '2019-12-31' AND DATEDIFF(CURDATE(), DATE(ac.date_added)) < 30) OR (DATE(ac.date_added) < '2020-01-01' AND DATE(ao.date_added) > '2019-12-31'))
      AND aa.country_id = 38 AND ao.order_type < '10' AND ao.order_status_id IN (3, 5)
      AND aa1.ambassador_id != 1 GROUP BY YEAR(ao.date_added), MONTH(ao.date_added), aa1.ambassador_id
      HAVING SUM(ao.sales_val_cad) >= 600 ORDER BY SUM(ao.sales_val_cad) DESC";

        $query = $this->db->query($cad_sql);
        if ($query->num_rows) {
            return $query->rows;
        }

        return false;
    }

}
