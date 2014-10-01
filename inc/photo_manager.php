<?php

define('PHOTO_LIMIT', 20);
define('PAGE_LIMIT', 5);
define('TAG_DELIMETER', ',');
define('HASH_DELIMETER', ':');

class Photo_Manager
{
	var $count = 0;
	var $list = [];
	var $tag = [];
	var $page = 0;
	var $filter = [];

	function __construct()
	{
		$this->page = Input::cleanGPC('g', 'page', TYPE_INT);
		if ($this->page == 0)
			$this->page = 1;
	}

	function get_count()
	{
		$result = DB::first("SELECT TABLE_ROWS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'test-photo'");
		$this->count = $result['TABLE_ROWS'];
	}

	function load_list()
	{
		$this->get_count();

		$result = DB::read('SELECT id, user_id, src, created_at FROM `test-photo` ORDER BY created_at DESC LIMIT '.(($this->page-1) * PHOTO_LIMIT).','.PHOTO_LIMIT);
		while ($row = DB::fetch($result))
			$this->list[] = $row;
		DB::free($result);
	}

	function load_list_filter()
	{
		$this->apply_filter();
		$this->count = count($this->filter);

		$result = DB::read('SELECT id, user_id, src, created_at FROM `test-photo` WHERE id IN ('.join(',', $this->filter).') ORDER BY created_at DESC LIMIT '.(($this->page-1) * PHOTO_LIMIT).','.PHOTO_LIMIT);
		while ($row = DB::fetch($result))
			$this->list[] = $row;
		DB::free($result);
	}

	function load_tag()
	{
		$result = DB::read('SELECT name FROM tag');
		while ($row = DB::fetch($result))
		{
			$this->tag[] = $row;
		}
		DB::free($result);
	}

	function get_tags($id)
	{
		$result = DB::first("SELECT taglist FROM photo_tag WHERE id='".DB::escape($id)."'");
		return $result ? $result['taglist'] : '';
	}

	function save_tags($id, $taglist)
	{
		$add_tags = [];
		$exist_tags = [];

		$list = explode(TAG_DELIMETER, $taglist);
		$list = array_map('trim', $list);
		$list = array_unique($list);

		$result = DB::first("SELECT taglist FROM photo_tag WHERE id='".DB::escape($id)."'");
		if ($result)
			$exist_tags = explode(TAG_DELIMETER, $result['taglist']);

		foreach ($list as $tagname)
		{
			$index = array_search($tagname, $exist_tags); /* check given tag already exists */
			if ($index === false) /* tag is new for that photo */
				$add_tags[] = $tagname;
			else
				unset($exist_tags[$index]);
		}

		/* now existing tags contain sub_tags */
		$result = DB::write("REPLACE INTO photo_tag SET taglist='".DB::escape(join(TAG_DELIMETER, $list))."', id='".DB::escape($id)."'");
		
		foreach ($add_tags as $tagname)
			$this->tag_add_id($tagname, $id);

		foreach ($exist_tags as $tagname)
			$this->tag_remove_id($tagname, $id);
	}

	function tag_add_id($tagname, $id)
	{
		if ($tagname == '')
			return false;

		$tag = DB::first("SELECT idlist FROM tag WHERE name='".DB::escape($tagname)."'");
		if ($tag)
		{
			$ids = $this->unserialize($tag['idlist']);
			$ids[] = $id;
			DB::write("UPDATE tag SET idlist='".DB::escape($this->serialize($ids))."' WHERE name='".DB::escape($tagname)."'");
		}
		else
		{
			$ids = [$id];
			DB::write("INSERT INTO tag (name, idlist) VALUES ('".DB::escape($tagname)."', '".DB::escape($this->serialize($ids))."')");
		}
	}

	function serialize($string)
	{
		return join(HASH_DELIMETER, $string);
	}

	function unserialize($string)
	{
		return explode(HASH_DELIMETER, $string);
	}

	function apply_filter()
	{
		$filter = Input::cleanGPC('c', 'filter', TYPE_STR);
		if ($filter == '')
			return false;
		$filter = explode(',', $filter);
		foreach ($filter as &$tagname)
			$tagname = "'".DB::escape($tagname)."'";

		$first = true;

		$result = DB::read('SELECT idlist FROM tag WHERE name IN ('.join(',', $filter).')');
		while ($row = DB::fetch($result))
		{
			if ($first)
			{
				$first = false;
				$this->filter = $this->unserialize($row['idlist']);
			}
			else
				$this->filter = array_intersect($this->filter, $this->unserialize($row['idlist']));
		}
		DB::free($result);
	}

	function tag_remove_id($tagname, $id)
	{
		$tag = DB::first("SELECT idlist FROM tag WHERE name='".DB::escape($tagname)."'");
		if ($tag)
		{
			$ids = $this->unserialize($tag['idlist']);
			$index = array_search($id, $ids);
			if ($index !== false)
			{
				unset($ids[$index]);
				if (count($ids))
					DB::write("UPDATE tag SET idlist='".DB::escape($this->serialize($ids))."' WHERE name='".DB::escape($tagname)."' LIMIT 1");
				else
					DB::write("DELETE FROM tag WHERE name='".DB::escape($tagname)."' LIMIT 1");
			}
			else
			{
				; /* error here: cant find photo id in tag's hash */
			}
		}
		else
		{
			; /* error here: cant find tag */
		}
	}

	function pager()
	{
		$html_pager = '';

		$page_count = floor(($this->count-1) / PHOTO_LIMIT);
		if ($page_count < PAGE_LIMIT)
		{
			for ($i = 1; $i <= $page_count+1; $i++)
			{
				if ($i == $this->page)
					$html_pager .= '<a href="#" class="page_btn active" onclick="return false;">'.$i.'</a>';
				else
					$html_pager .= '<a href="?page='.$i.'" class="page_btn">'.$i.'</a>';
			}
		}
		else
		{
			for ($i = 1; $i <= PAGE_LIMIT; $i++)
			{
				if ($i == $this->page)
					$html_pager .= '<a href="#" class="page_btn active" onclick="return false;">'.$i.'</a>';
				else
					$html_pager .= '<a href="?page='.$i.'" class="page_btn">'.$i.'</a>';
			}
			$html_pager .= '<input type="text" class="text" id="pager_goto" name="page" '.($this->page ? 'value="'.htmlspecialchars($this->page).'"' : '').' placeholder="jump to..."/>
			<input type="button" id="pager_go" value="go" />';
		}

		return $html_pager;
	}

}

?>