<?php
/*
  Plugin Name: Last user IP
  Description: Logs last user IP and shows it in user table.
  Version: 1.0
  Author: zudikas-zveris
  Author URI: http://www.eturgelis.lt
  License:  GPLv2
 */

function zzwp_modify_user_table( $column ) {
    $column['last_ip'] = 'IP';
    return $column;
}
add_filter( 'manage_users_columns', 'zzwp_modify_user_table' );

function zzwp_modify_user_table_sortable( $column ) {
    $column['last_ip'] = 'last_ip';
    return $column;
}
add_filter( 'manage_users_sortable_columns', 'zzwp_modify_user_table_sortable' );
 
function zzwp_modify_user_table_row( $val, $column_name, $user_id ) {
     
    if ( $column_name == 'last_ip' ) {
            
        $ip = get_user_meta( $user_id, 'user_last_ip', true );

        if ( $ip ) {
            return $ip;
        }    
        else {
            update_user_meta( $user_id, 'user_last_ip', '-' );
            return '-';
        }            
    }

}
add_filter( 'manage_users_custom_column', 'zzwp_modify_user_table_row', 10, 3 );

function zzwp_user_query($userquery) {
    
    if ( 'last_ip' == $userquery->query_vars['orderby'] ) {        
        global $wpdb;
        $userquery->query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS alias ON ($wpdb->users.ID = alias.user_id) "; 
        $userquery->query_where .= " AND alias.meta_key = 'user_last_ip' ";
        $userquery->query_orderby = " ORDER BY alias.meta_value " . ($userquery->query_vars["order"] == "ASC" ? "asc " : "desc ");
    }
    
}
if(is_admin()) {    
    add_action('pre_user_query', 'zzwp_user_query');
}

function zzwp_log_user_ip() {
    
    global $current_user;
    
    if ( is_user_logged_in() )
        update_user_meta($current_user->ID, 'user_last_ip', $_SERVER['REMOTE_ADDR']);
}
add_action('init', 'zzwp_log_user_ip' );


?>
