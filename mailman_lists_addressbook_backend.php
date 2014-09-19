<?php

/**
 * Example backend class for a custom address book
 *
 * This one just holds a static list of address records
 *
 * @author Borut Mrak
 */
class mailman_lists_addressbook_backend extends rcube_addressbook
{
  public $primary_key = 'ID';
  public $readonly = true;
  public $groups = false;

  private $filter;
  private $result;
  private $name;
  private $_lists;

  public function __construct($name)
  {
    $config = rcmail::get_instance()->config;
    $this->_cachefile = $config->get('mailman_lists_addressbook_cache',false);

    if($this->_cachefile) {
      // Load lists from pre-prepared file, otherwise it takes too long.
      $this->_lists = unserialize(file_get_contents($this->_cachefile));

      $this->ready = true;
      $this->name = $name;
    }

  }

  public function get_name()
  {
    return $this->name;
  }

  public function set_search_set($filter)
  {
    $this->filter = $filter;
  }

  public function get_search_set()
  {
    return $this->filter;
  }

  public function reset()
  {
    $this->result = null;
    $this->filter = null;
  }

  function list_groups($search = null)
  {
    return array(
      array('ID' => 'testgroup1', 'name' => "Testgroup"),
      array('ID' => 'testgroup2', 'name' => "Sample Group"),
    );
  }

  public function list_records($cols=null, $subset=0)
  {
    
    $this->result = $this->count();
    foreach($this->_lists as $list) {
      $this->result->add($list);
    }

    return $this->result;
  }

  public function search($fields, $value, $strict=false, $select=true, $nocount=false, $required=array())
  {
    $this->result = new rcube_result_set();
    foreach($this->_lists as $list) {
      foreach($fields as $searchfield) {
        if(key_exists($searchfield,$list) and preg_match("/$value/", $list[$searchfield])) {
          $this->result->add($list);
        }
      }
    }
    // no search implemented, just list all records
    return $this->result;
  }

  public function count()
  {
    return new rcube_result_set(1, ($this->list_page-1) * $this->page_size);
  }

  public function get_result()
  {
    return $this->result;
  }

  public function get_record($id, $assoc=false)
  {
    $this->result = new rcube_result_set();

    foreach($this->_lists as $list) {
      if($list['ID'] == $id) {
        $sql_arr = $list;
        $this->result->add($list);
      }
    }

    return $assoc && $sql_arr ? $sql_arr : $this->result;
  }


  function create_group($name)
  {
    $result = false;

    return $result;
  }

  function delete_group($gid)
  {
    return false;
  }

  function rename_group($gid, $newname)
  {
    return $newname;
  }

  function add_to_group($group_id, $ids)
  {
    return false;
  }

  function remove_from_group($group_id, $ids)
  {
     return false;
  }

}
