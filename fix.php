<?php
namespace NamePlugin;

class NameApi {
    public $api_url;
  
    public function __construct() {
        $this->api_url = 'https://....';
    }
  
    public function list_vacancies($post, $vid = 0) {
        global $wpdb;

        if (!is_object($post)) {
            return false;
        }

        $page = 0;
        $vacancies = [];
        $foundVacancy = null;

        do {
            $params = [
                'status' => 'all',
                'id_user' => $this->self_get_option('superjob_user_id'),
                'with_new_response' => 0,
                'order_field' => 'date',
                'order_direction' => 'desc',
                'page' => $page,
                'count' => 100
            ];

            $url = $this->api_url . '/hr/vacancies/?' . http_build_query($params);
            $response = $this->api_send($url);
            $responseObj = json_decode($response);

            if ($response === false || !is_object($responseObj) || !isset($responseObj->objects)) {
                return false;
            }

            $vacancies = array_merge($vacancies, $responseObj->objects);

            if ($vid > 0) {
                foreach ($responseObj->objects as $vacancy) {
                    if ($vacancy->id == $vid) {
                        $foundVacancy = $vacancy;
                        break 2; // Прерываем оба цикла
                    }
                }
            }

            $page++;
        } while ($responseObj->more);

        if ($vid > 0) {
            return $foundVacancy;
        }

        return $vacancies;
    }

    public function api_send($url) {
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }
        return wp_remote_retrieve_body($response);
    }

    public function self_get_option($option_name) {
        return get_option($option_name, '');
    }
}
