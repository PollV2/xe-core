<?php
    /**
     * @class  memberModel
     * @author zero (zero@nzeo.com)
     * @brief  member module의 Model class
     **/

    class memberModel extends member {

        /**
         * @brief 자주 호출될거라 예상되는 데이터는 내부적으로 가지고 있자...
         **/
        var $member_info = NULL;
        var $member_groups = NULL;
        var $join_form_list = NULL;

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 로그인 되어 있는지에 대한 체크
         **/
        function isLogged() {
            if($_SESSION['is_logged']&&$_SESSION['ipaddress']==$_SERVER['REMOTE_ADDR']) return true;

            $_SESSION['is_logged'] = false;
            $_SESSION['logged_info'] = '';
            return false;
        }

        /**
         * @brief 인증된 사용자의 정보 return
         **/
        function getLoggedInfo() {
            // 로그인 되어 있고 세션 정보를 요청하면 세션 정보를 return
            if($this->isLogged()) return $_SESSION['logged_info'];
            return NULL;
        }

        /**
         * @brief user_id에 해당하는 사용자 정보 return
         **/
        function getMemberInfoByUserID($user_id) {
            if(!$this->member_info[$member_srl]) {
                // DB 객체 생성
                $oDB = &DB::getInstance();

                $args->user_id = $user_id;
                $output = $oDB->executeQuery('member.getMemberInfo', $args);
                if(!$output) return $output;

                $member_info = $this->arrangeMemberInfo($output->data);
                $member_info->group_list = $this->getMemberGroups($member_info->member_srl);

                $this->member_info[$$member_info->member_srl] = $member_info;
            }
            return $this->member_info[$member_srl];
        }

        /**
         * @brief member_srl로 사용자 정보 return
         **/
        function getMemberInfoByMemberSrl($member_srl) {
            if(!$this->member_info[$member_srl]) {
                // DB 객체 생성
                $oDB = &DB::getInstance();

                $args->member_srl = $member_srl;
                $output = $oDB->executeQuery('member.getMemberInfoByMemberSrl', $args);
                if(!$output) return $output;

                $member_info = $this->arrangeMemberInfo($output->data);
                $member_info->group_list = $this->getMemberGroups($member_info->member_srl);

                $this->member_info[$member_srl] = $member_info;
            }
            return $this->member_info[$member_srl];
        }

        /**
         * @brief 사용자 정보 중 extra_vars를 알맞게 편집
         **/
        function arrangeMemberInfo($info) {
            $extra_vars = unserialize($info->extra_vars);
            unset($info->extra_vars);
            if(!$extra_vars) return $info;
            foreach($extra_vars as $key => $val) {
                if(eregi('\|\@\|', $val)) $val = explode('|@|', $val);
                if(!$info->{$key}) $info->{$key} = $val;
            }
            return $info;
        }

        /**
         * @brief userid에 해당하는 member_srl을 구함
         **/
        function getMemberSrlByUserID($user_id) {
            // DB 객체 생성
            $oDB = &DB::getInstance();

            $args->user_id = $user_id;
            $output = $oDB->executeQuery('member.getMemberSrl', $args);
            return $output->data->member_srl;
        }

        /**
         * @brief userid에 해당하는 member_srl을 구함
         **/
        function getMemberSrlByEmailAddress($email_address) {
            // DB 객체 생성
            $oDB = &DB::getInstance();

            $args->email_address = $email_address;
            $output = $oDB->executeQuery('member.getMemberSrl', $args);
            return $output->data->member_srl;
        }

        /**
         * @brief userid에 해당하는 member_srl을 구함
         **/
        function getMemberSrlByNickName($nick_name) {
            // DB 객체 생성
            $oDB = &DB::getInstance();

            $args->nick_name = $nick_name;
            $output = $oDB->executeQuery('member.getMemberSrl', $args);
            return $output->data->member_srl;
        }

        /**
         * @brief 현재 접속자의 member_srl을 return
         **/
        function getLoggedMemberSrl() {
            if(!$this->isLogged()) return;
            return $_SESSION['member_srl'];
        }

        /**
         * @brief 현재 접속자의 user_id을 return
         **/
        function getLoggedUserID() {
            if(!$this->isLogged()) return;
            $logged_info = $_SESSION['logged_info'];
            return $logged_info->user_id;
        }

        /**
         * @brief 회원 목록을 구함
         **/
        function getMemberList() {
            // 등록된 member 모듈을 불러와 세팅
            $oDB = &DB::getInstance();

            $args->sort_index = "member_srl";
            $args->page = Context::get('page');
            $args->list_count = 40;
            $args->page_count = 10;
            return $oDB->executeQuery('member.getMemberList', $args);
        }

        /**
         * @brief member_srl이 속한 group 목록을 가져옴
         **/
        function getMemberGroups($member_srl) {
            if(!$this->member_groups[$member_srl]) {
                // DB 객체 생성
                $oDB = &DB::getInstance();

                $args->member_srl = $member_srl;
                $output = $oDB->executeQuery('member.getMemberGroups', $args);
                if(!$output->data) return;

                $group_list = $output->data;
                if(!is_array($group_list)) $group_list = array($group_list);

                foreach($group_list as $group) {
                    $result[$group->group_srl] = $group->title;
                }
                $this->member_groups[$member_srl] = $result;
            }
            return $this->member_groups[$member_srl];
        }

        /**
         * @brief 기본 그룹을 가져옴
         **/
        function getDefaultGroup() {
            // DB 객체 생성
            $oDB = &DB::getInstance();

            $output = $oDB->executeQuery('member.getDefaultGroup');
            return $output->data;
        }

        /**
         * @brief group_srl에 해당하는 그룹 정보 가져옴
         **/
        function getGroup($group_srl) {
            // DB 객체 생성
            $oDB = &DB::getInstance();

            $args->group_srl = $group_srl;
            $output = $oDB->executeQuery('member.getGroup', $args);
            return $output->data;
        }

        /**
         * @brief 그룹 목록을 가져옴
         **/
        function getGroups() {
            // DB 객체 생성
            $oDB = &DB::getInstance();

            $output = $oDB->executeQuery('member.getGroups');
            if(!$output->data) return;

            $group_list = $output->data;
            if(!is_array($group_list)) $group_list = array($group_list);

            foreach($group_list as $val) {
                $result[$val->group_srl] = $val;
            }
            return $result;
        }

        /**
         * @brief 회원 가입폼 추가 확장 목록 가져오기
         *
         * 이 메소드는 modules/member/tpl.admin/filter/insert.xml 의 extend_filter로 동작을 한다.
         * extend_filter로 사용을 하기 위해서는 인자값으로 boolean값을 받도록 규정한다.
         * 이 인자값이 true일 경우 filter 타입에 맞는 형태의 object로 결과를 return하여야 한다.
         **/
        function getJoinFormList($filter_response = false) {
            global $lang;

            if(!$this->join_form_list) {
                // DB 객체 생성
                $oDB = &DB::getInstance();

                // list_order 컬럼의 정렬을 위한 인자 세팅
                $args->sort_index = "list_order";
                $output = $oDB->executeQuery('member.getJoinFormList', $args);

                // 결과 데이터가 없으면 NULL return
                $join_form_list = $output->data;
                if(!$join_form_list) return NULL;

                // default_value의 경우 DB에 array가 serialize되어 입력되므로 unserialize가 필요
                if(!is_array($join_form_list)) $join_form_list = array($join_form_list);
                $join_form_count = count($join_form_list);
                for($i=0;$i<$join_form_count;$i++) {
                    $member_join_form_srl = $join_form_list[$i]->member_join_form_srl;
                    $column_type = $join_form_list[$i]->column_type;
                    $column_name = $join_form_list[$i]->column_name;
                    $column_title = $join_form_list[$i]->column_title;
                    $default_value = $join_form_list[$i]->default_value;

                    // 언어변수에 추가
                    $lang->extend_vars[$column_name] = $column_title;

                    // checkbox, select등 다수 데이터 형식일 경우 unserialize해줌
                    if(in_array($column_type, array('checkbox','select'))) {
                        $join_form_list[$i]->default_value = unserialize($default_value);
                        if(!$join_form_list[$i]->default_value[0]) $join_form_list[$i]->default_value = '';
                    } else {
                        $join_form_list[$i]->default_value = '';
                    }

                    $list[$member_join_form_srl] = $join_form_list[$i];
                }
                $this->join_form_list = $list;
            }

            // filter_response가 true일 경우 object 스타일을 구함
            if($filter_response && count($this->join_form_list)) {

                foreach($this->join_form_list as $key => $val) {
                    if($val->is_active != 'Y') continue;
                    unset($obj);
                    $obj->type = $val->column_type;
                    $obj->name = $val->column_name;
                    $obj->lang = $val->column_title;
                    $obj->required = $val->required=='Y'?true:false;
                    $filter_output[] = $obj;
                }
                return $filter_output;

            }

            // 결과 리턴
            return $this->join_form_list;
        }

        /**
         * @brief 한개의 가입항목을 가져옴
         **/
        function getJoinForm($member_join_form_srl) {
            // DB 객체 생성
            $oDB = &DB::getInstance();
            $args->member_join_form_srl = $member_join_form_srl;
            $output = $oDB->executeQuery('member.getJoinForm', $args);
            $join_form = $output->data;
            if(!$join_form) return NULL;

            $column_type = $join_form->column_type;
            $default_value = $join_form->default_value;

            if(in_array($column_type, array('checkbox','select'))) {
                $join_form->default_value = unserialize($default_value);
            } else {
                $join_form->default_value = '';
            }

            return $join_form;
        }

        /**
         * @brief 금지 아이디 목록 가져오기
         **/
        function getDeniedIDList() {
            if(!$this->denied_id_list) {
                // DB 객체 생성
                $oDB = &DB::getInstance();

                $args->sort_index = "list_order";
                $args->page = Context::get('page');
                $args->list_count = 40;
                $args->page_count = 10;

                $output = $oDB->executeQuery('member.getDeniedIDList', $args);
                $this->denied_id_list = $output;
            }
            return $this->denied_id_list;
        }

        /**
         * @brief 금지 아이디인지 확인
         **/
        function isDeniedID($user_id) {
            // DB 객체 생성
            $oDB = &DB::getInstance();

            $args->user_id = $user_id;
            $output = $oDB->executeQuery('member.chkDeniedID', $args);
            if($output->data->count) return true;
            return false;
        }

    }
?>
