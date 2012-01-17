<?
if (!defined('COLUMN_SORT_CLASS_INCLUDED'))
{
	define('COLUMN_SORT_CLASS_INCLUDED', 1);
//==================================================================
// Column sort class
//==================================================================

	class column_sort_db
	{
		var $url;		// default url
		var $cols_n;	// name
		var $cols_t;	// title
		var $def_col;	// default column
		var $def_dir;	// default direction

		function column_sort_db()
		{
			$this->cols_n = array();
			$this->cols_t = array();
			$this->url = '';
			$this->def_col = 0;
			$this->def_dir = 1;
		}

		function add_column($name,$title)
		{
			$this->cols_n[] = $name;
			$this->cols_t[] = $title;
		}

		function set_url($url,$params = false)
		{
			if($params)
				$this->url = $url.'&csd=';
			else
				$this->url = $url.'?csd=';
		}

		function set_default_sort($column,$direction)
		{
			$this->def_col = $column;
			$this->def_dir = ($direction > 0 && $direction < 3) ? $direction : 1;
		}

		function get_all_content()
		{
			$text = 'sort :';
			for($ii=0; $ii< count ($this->cols_n); $ii++)
			{
				$text .= ' ';
				$text .= $this->get_col_content($ii,true);
			}

			return $text;
		}

		function get_col_content($col,$show_title = false)
		{
			$text = '';
			if($col >= 0 && $col < count ($this->cols_n))
			{
				$v_up = (($col+1) << 4)+1;
				$v_dn = (($col+1) << 4)+2;
				if($show_title)
					$text .= $this->cols_t[$col].' [';
				$text .= '<A HREF="'.$this->url.$v_up.'"><IMG src="imgs/up'.(($this->def_col == $col && $this->def_dir == 1) ? '1' : '0' ).'.gif" border="0" width="8" height="8" alt="A->Z"></A>';
				$text .= '&nbsp;';
				$text .= '<A HREF="'.$this->url.$v_dn.'"><IMG src="imgs/dn'.(($this->def_col == $col && $this->def_dir == 2) ? '1' : '0' ).'.gif" border="0" width="8" height="8" alt="Z->A"></A>';
				if($show_title)
					$text .= ']';
			}
			return $text;
		}

		function get_sql_string()
		{
			global $csd;

			$csd = (IsSet($csd) && is_numeric($csd)) ? $csd : 0;
			$col = $csd >> 4;
			$dir = $csd - ($col << 4);

			$col--;
			$col = ($col >= 0 && $col <  count ($this->cols_n)) ? $col : 0;
			$dir = ($dir > 0 && $dir < 3) ? $dir : 1;

			$this->set_default_sort($col,$dir);

//			$query = 'res = '.$csd;
//			$query .= ', col = '.$col.', dir = '.$dir;
			$query = ' ORDER BY `'.$this->cols_n[$col].'` ';
			$query.= ($dir == 2) ? 'DESC' : 'ASC';
			return $query;
		}
	}

//==================================================================
}	// define (COLUMN_SORT_CLASS_INCLUDED)
?>