<?php
    include 'phpQuery/phpQuery/phpQuery.php';
    function parserOk($url){
        $url_video = $url.'/video';
        $url_photos = $url.'/photos';
        //Выгрузка главной страницы
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_FOLLOWLOCATION,1);
        $site = curl_exec($curl);
        //Выгрузка страницы c видео
        $curl_video = curl_init();
        curl_setopt($curl_video,CURLOPT_URL,$url_video);
        curl_setopt($curl_video,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_video,CURLOPT_FOLLOWLOCATION,1);
        $site_video = curl_exec($curl_video);
        //Выгрузка страницы c альбомами
        $curl_photos = curl_init();
        curl_setopt($curl_photos,CURLOPT_URL,$url_photos);
        curl_setopt($curl_photos,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_photos,CURLOPT_FOLLOWLOCATION,1);
        $site_photos = curl_exec($curl_photos);

        $pq = phpQuery::newDocument($site);
        $pq_video = phpQuery::newDocument($site_video);
        $pq_photos = phpQuery::newDocument($site_photos);
        $navList = $pq->find('.navMenuCount');
        $subscriber = $pq->find('[data-type="SUBSCRIBERS"] a');
        $relativPq =$pq->find('.__relatives .user-profile_i');
        $relativ_hidden_Pq =$pq->find('.user-profile_sub-list .user-profile_i');
        $birthplace_type = $pq->find('.ic_city + span');
        $birthplace_p = $pq->find('[data-type="TEXT"]');
        $count_video = $pq_video->find('.ugrid_i');
        $count_albums = $pq_photos->find('.photo-sc__albums .portlet_h_name_aux');
        $count_albums = $count_albums->html();
        $count_video = count($count_video);
        $education_full = $pq->find('.user-profile_group_h + .user-profile_list:not(.__relatives)');
        $education_t = $pq->find('.user-profile_group_h');
        $canonical = $pq->find('[rel="canonical"]');
        $canonical =  $canonical->attr('href'); 
        $canonical = explode("/", $canonical);
        $canonical = $canonical[count($canonical)-1];
        $relation_type =  $pq->find('.ic_open-for-communication + span');
        $relation_type = $relation_type->html();
        for($j = 0;$j<count($birthplace_type);$j++){
            $place['type'] = $birthplace_type[$j]->html();
            $place['city'] = $birthplace_p[$j]->html();
            $birthplace[]=$place;
            unset($place);
        }
        if($relation_type==""){
            $relation_type =  $pq->find('.ic_relationship + span');
            $relation_type = $relation_type->html();
            if($relation_type==""){
                $relation_type =  $pq->find('.ic_break_relations + span');
                $relation_type = $relation_type->html();
                $relation = $pq->find('.user-profile_i_relation-t');
                $relation = $relation->html();
            }
            else{
                if($relation_type=="В отношениях с"){
                    $relation['relation'] = "В отношениях";
                    $relation_partner = $pq->find('[data-type="RELATION"] a');
                    $relation_partner = $relation_partner->attr('href');
                    $relation_partner = preg_replace("/[^0-9]/", '',$relation_partner);
                    $relation['relation_partner'] = $relation_partner;
                }
                else{
                    $relation['relation'] = "В браке";
                    $relation_partner = $pq->find('[data-type="RELATION"] a');
                    $relation_partner = $relation_partner->attr('href');
                    $relation_partner = preg_replace("/[^0-9]/", '',$relation_partner);
                    $relation['relation_partner'] = $relation_partner;
                }
            }
        }
        else{
            $relation = $pq->find('.user-profile_i_relation-t');
            $relation = $relation->html();
        }
        //Список количество 1)друзей 2)количество фото 3)количество групп 4)количество игр 5) количество заметок
        foreach ($navList  as $list) {
            $pqLink = pq($list); //pq делает объект phpQuery
            $counter[] = $pqLink->html();
        }
        //Количество подписчиков
        $t =  pq($subscriber)->html();
        $counter[] = $t;
        //получаем родственников и видемой области
        foreach ($relativPq as $rel) {
            $rlLink = pq($rel); //pq делает объект phpQuery
            $rlLink = $rlLink->find('.lstp-t');
            $rlLink = pq($rlLink);
            $relativ_type[] = $rlLink->html();
        }
        //получаем родственников и скрытой области
        foreach ($relativ_hidden_Pq as $rel) {
            $rlLink = pq($rel); //pq делает объект phpQuery
            $rlLink = $rlLink->find('.lstp-t');
            $rlLink = pq($rlLink);
            $relativ_type[] = $rlLink->html();
        }
        //получаем id родственников и видемой области
        foreach ($relativPq as $rel) {
            $rlLink = pq($rel); //pq делает объект phpQuery
            $rlLink = $rlLink->find('a');
            $rlLink = pq($rlLink);
            $str = $rlLink->attr('href');
            $str = preg_replace("/[^0-9]/", '', $str);
            $relativ_id[] = $str;
        }
        //получаем id родственников и скрытой области
        foreach ($relativ_hidden_Pq as $rel) {
            $rlLink = pq($rel); //pq делает объект phpQuery
            $rlLink = $rlLink->find('a');
            $rlLink = pq($rlLink);
            $str = $rlLink->attr('href');
            $str = preg_replace("/[^0-9]/", '', $str);
            $relativ_id[] = $str;
        }
        //var_dump()
    // echo count( $relativ_id);
        for($j = 0; $j< count( $relativ_id); $j++){

            $items['rel_id'] = $relativ_id[$j];
            $items['type'] = $relativ_type[$j];
            $relativs[] = $items;
            unset($items);
        }
        //получаем id родственников и скрытой области
        foreach ($education_t as $ed) {
            $rlLink = pq($ed); //pq делает объект phpQuery
            $education_type[] = $rlLink->html();
        }
        if($education_type[0]=="Родственники"){
            $i =1;
        }
        else{
            $i=0;
        }
        foreach ($education_full as $ed) {
            $ed_list = pq($ed); //pq делает объект phpQuery
            $ed_list = $ed_list->find('.user-profile_i');
            foreach($ed_list as $list){
                $list_prop = pq($list);
                $item['type'] = $education_type[$i];
                $action = $list_prop->find('.user-profile_i_t_inner');
                $action = $action->html();
                $item['action'] = $action;
                $name_prop = $list_prop->find('.ellip a span');
                $name_prop = $name_prop->html();
                $item['name'] = $name_prop;
                $date = $list_prop->find('.darkgray');
                $date = $date->html();
                $item['duration']= $date;
                $education[] = $item;
                unset($item);
            }
            $i++;
        }
        for($i = 0; $i<count($education); $i++){
            //var_dump(preg_match('/Раб/i',$education[$i]['action']));
            if(preg_match('/Раб/i',$education[$i]['action']))
             $education[$i]['action'] = "работа";
            if(preg_match('/коллед/i',$education[$i]['action']))
             $education[$i]['action'] = "колледж";
            if(preg_match('/школ/i',$education[$i]['action']))
             $education[$i]['action'] = "школа";
            if(preg_match('/Служ/i',$education[$i]['action']))
             $education[$i]['action'] = "служба";
            if(preg_match('/вуз/i',$education[$i]['action']))
             $education[$i]['action'] = "вуз";
            $str = preg_replace("/[^0-9]/", ' ', $education[$i]['duration']);
            $str = explode(" ", $str);
            if($str[9]=='')
                $str[9] = "YYYY";
            $education[$i]['duration'] = $str[3].'-'.$str[9];
        }
        $result['friends'] = $counter[0];
        $result['photos'] = $counter[1];
        $result['groups'] = $counter[2];
        $result['games'] = $counter[3];
        $result['notes'] = $counter[4];
        $result['subscribers'] = $counter[5];
        $result['relativs'] = $relativs;
        $result['birthplace'] = $birthplace;
        $result['relation'] = $relation;
        $result['canonical'] = $canonical;
        $result['education'] = $education;
        $result['videos'] =  $count_video;
        $result['albums'] = $count_albums;
        return $result;
    }
    $url = 'https://ok.ru/profile/564245226318';
    $prof = parserOk($url);
    var_dump($prof);