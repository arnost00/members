<?
if (!defined('HTML_TABLE_CLASS_INCLUDED'))
{
	define('HTML_TABLE_CLASS_INCLUDED', 1);
//==================================================================
// HTML TABLE class
//==================================================================

	require("cfg/_colors.php");

	DEFINE('ALIGN_RIGHT','right');
	DEFINE('ALIGN_CENTER','center');
	DEFINE('ALIGN_LEFT','left');

//==================================================================
	class html_table_base
	{	// common table class
		protected $class_name ='ctbl';
		protected $bgc_header;
		protected $bgc_row_select, $bgc_row1, $bgc_row2,$bgc_row_hglt;
		protected $cellpadding, $cellspacing;
		public $table_width;
		protected $enable_row_select;
		protected $tc_normal, $tc_header, $tc_highlight;
		protected $font_size;
		protected $highlighted_next_row;
		protected $paddingLR;
		protected $paddingTB;

		//__________________________________________________________________
		function html_table_base($table_class_name)
		{
			global $g_colors;
			$this->bgc_header = $g_colors["table_header"];
			$this->bgc_row_select = $g_colors['table_row_select'];
			$this->bgc_row1 = $g_colors['table_row1'];
			$this->bgc_row2 = $g_colors['table_row2'];
			$this->bgc_row_hglt = $g_colors['table_row_highlight'];
			$this->tc_normal = $g_colors['table_text'];
			$this->tc_header = $g_colors['table_text_header'];
			$this->tc_highlight = $g_colors['table_text_highlight'];
			$this->cellpadding = 0;
			$this->cellspacing = 2;
			$this->table_width = 0;	// 0 - nedef, 100 - 100%
			$this->enable_row_select = true;
			$this->font_size = 9;
			$this->highlighted_next_row = false;
			$this->paddingLR = 4;	// left, right
			$this->paddingTB = 1;	// top, bottom
			if ($table_class_name != '')
				$this->class_name = $table_class_name;
		}

		//__________________________________________________________________
		protected function _get_row_css($align,$pLR,$pTB)
		{	// inner class function
			return 'td.'.$this->class_name.$align.' { padding-left: '.$pLR.'; padding-right: '.$pLR.'; padding-top: '.$pTB.'; padding-bottom: '.$pTB.(($align != '')?'; text-align: '.$align:'').'; } ';
		}

		//__________________________________________________________________
		protected function _get_header_css($align,$pLR,$pTB)
		{	// inner class function
			return 'td.'.$this->class_name.'_header_'.$align.' { vertical-align: middle; padding-left: '.$pLR.'; padding-right: '.$pLR.'; padding-top: '.$pTB.'; padding-bottom: '.$pTB.'; text-align: '.$align.'; color : '.$this->tc_header.'; font-weight : bold; } ';
		}

		//__________________________________________________________________
		protected function _get_misc_css($name,$pLR,$pTB,$align,$additional)
		{	// inner class function
			return 'td.'.$this->class_name.$name.' { padding-left: '.$pLR.'; padding-right: '.$pLR.'; padding-top: '.$pTB.'; padding-bottom: '.$pTB.'; text-align: '.$align.'; '.$additional.' } ';
		}

		//__________________________________________________________________
		function get_css()
		{
			$css = '<style type="text/css"> '.
			'table.'.$this->class_name.' { font-size: '.$this->font_size.'pt; color: '.$this->tc_normal.'; } '.

			$this->_get_row_css(ALIGN_LEFT,$this->paddingLR,$this->paddingTB).
			$this->_get_row_css(ALIGN_CENTER,$this->paddingLR,$this->paddingTB).
			$this->_get_row_css(ALIGN_RIGHT,$this->paddingLR,$this->paddingTB).
			$this->_get_row_css('',$this->paddingLR,$this->paddingTB).
			$this->_get_header_css(ALIGN_LEFT,$this->paddingLR,$this->paddingTB).
			$this->_get_header_css(ALIGN_CENTER,$this->paddingLR,$this->paddingTB).
			$this->_get_header_css(ALIGN_RIGHT,$this->paddingLR,$this->paddingTB).
			$this->_get_misc_css('sort',$this->paddingLR,0,ALIGN_RIGHT,'').
			$this->_get_misc_css('form',$this->paddingLR,$this->paddingTB,ALIGN_LEFT,'color: '.$this->tc_highlight.'; font-weight : bold;').
			'table.'.$this->class_name.' tr.head { background: '.$this->bgc_header.'; } '.
			'table.'.$this->class_name.' tr:hover.head { background: '.$this->bgc_header.'; } '.
			'table.'.$this->class_name.' tr.r1 { background: '.$this->bgc_row1.'; } '.
			'table.'.$this->class_name.' tr.r2 { background: '.$this->bgc_row2.'; } '.
			'table.'.$this->class_name.' tr.highlight { background: '.$this->bgc_row_hglt.'; } '.
			(($this->enable_row_select)? ('table.'.$this->class_name.' tr:hover { background: '.$this->bgc_row_select.'; } '):'').
			'</style>';
			return $css;
		}

		//__________________________________________________________________
		function get_header()
		{
			return '<TABLE class="'.$this->class_name.'"'.(($this->table_width > 0) ? (' width="'.$this->table_width.'%"') : '').' cellpadding="'.$this->cellpadding.'" cellspacing="'.$this->cellspacing.'" border="0">';
		}

		//__________________________________________________________________
		protected function _get_pre_footer() {}	// inner class function

		//__________________________________________________________________
		function get_footer()
		{
			$row = $this->_get_pre_footer();
			$row .= '</TABLE>';
			return $row;
		}
		//__________________________________________________________________
		function set_next_row_highlighted()
		{
			$this->highlighted_next_row = true;
		}
	}

//==================================================================
	class html_table_mc extends html_table_base
	{	// multicolumn table with header
		protected $header_row;
		protected $header_row2;
		protected $sort_row;
		protected $cols_align;
		protected $row_idx;
		protected $cols;

		//__________________________________________________________________
		function html_table_mc($table_class_name = '')
		{
			html_table_base::html_table_base(($table_class_name != '') ? $table_class_name : 'ctmc');
			global $g_colors;
			$this->header_row = array();
			$this->header_row2 = array();
			$this->sort_row = array();
			$this->row_idx = 0;
			$this->cols = 0;
		}

		//__________________________________________________________________
		function set_header_col ($col,$text,$align,$width = 0,$ex_td='')
		{
			if (is_array($this->header_row))
			{
				$this->header_row[$col]['text'] = $text;
				$this->header_row[$col]['class'] = $align;
				$this->header_row[$col]['width'] = $width;
				$this->header_row[$col]['ex_td'] = $ex_td;
				$this->cols_align[$col] = $align;
				$this->sort_row[$col] = '';
			}
		}

		function set_sort_col ($col,$text)
		{
			$this->sort_row[$col] = $text;
		}

		//__________________________________________________________________
		function set_col_align($col,$align)
		{
			$this->cols_align[$col] = $align;
		}

		//__________________________________________________________________
		function set_col_aligns()// align sloupcu za sebou oddelene carkou.
		{
			$cols = func_num_args();
			if ($cols > 0)
			{
				for($i = 0; $i < $cols; $i++)
					$this->cols_align[$i] = func_get_arg($i);
			}
		}

		//__________________________________________________________________
		function get_header_row()
		{	// pri prvnim prubehu vytvori z pole 'header_row' string. Pri dalsich ho jen vraci.
			if (is_string($this->header_row))
				return $this->header_row;
			$row = '<TR class="head" height="20">';
			foreach($this->header_row as $col)
			{
				$row .= '<TD class="'.$this->class_name.'_header_'.$col['class'].'"';
				if ($col['width'] != 0)
					$row .= ' width="'.$col['width'].'"';
				if ($col['ex_td'] != '')
					$row .= ' '.$col['ex_td'];
				$row .= '>'.$col['text'].'</TD>';
				$this->cols++;
			}
			$row .= '</TR>';
			$this->header_row = $row;
			return $row;
		}

		function get_sort_row()
		{
			$row = '<TR class="head">';
			foreach($this->sort_row as $col)
			{
				$row .= '<TD class="'.$this->class_name.'sort"';
				$row .= '>'.$col.'</TD>';
			}

			$row .= '</TR>';
			return $row;
		}
		
		function get_header_row_with_sort()
		{// pri prvnim prubehu vytvori z pole 'header_row' string. Pri dalsich ho jen vraci.
			if (is_string($this->header_row2))
				return $this->header_row2;
			$row = '<TR class="head" height="20">';
			foreach($this->header_row as $idx => $col)
			{
				$row .= '<TD class="'.$this->class_name.'_header_'.$col['class'].'"';
				if ($col['width'] != 0)
					$row .= ' width="'.$col['width'].'"';
				if ($col['ex_td'] != '')
					$row .= ' '.$col['ex_td'];
				$row .= '>'.$col['text'];
				if ($this->sort_row[$idx] != '')
					$row .= ' '.$this->sort_row[$idx];
				$row .= '</TD>';
				$this->cols++;
			}
			$row .= '</TR>';
			$this->header_row2 = $row;
			return $row;
		}

		//__________________________________________________________________
		function get_new_row()// sloupce za sebou oddelene carkou.
		{
			$cols = func_num_args();
			if ($cols == 0) return '';
			for($i = 0; $i < $cols; $i++)
				$row[] = func_get_arg($i);
			return $this->get_new_row_arr($row);
		}

		//__________________________________________________________________
		function get_new_row_arr($row_arr)// pole sloupcu
		{
			$cols = count ($row_arr);
			if ($cols == 0) return '';
			if ($this->highlighted_next_row)
				$rc = 'highlight';
			else
				$rc= ((++$this->row_idx % 2) == 0) ? 'r1' : 'r2';
			$row = '<TR class="'.$rc.'" valign="top">';
			for($i = 0; $i < $cols; $i++)
			{
				$row .= '<TD class="'.$this->class_name.$this->cols_align[$i].'"';
				$row .= '>'.$row_arr[$i].'</TD>';
			}
			if ($cols < $this->cols)	// doplneni sloupcu na pocet
				for($i = $cols; $i < $this->cols; $i++)
				{
					$row .= '<TD></TD>';
				}
			$row .= '</TR>';
			$this->highlighted_next_row = false;
			return $row;
		}

		//__________________________________________________________________
		function get_break_row($small = false)// vytvori break-line do tabulky
		{
			if ($this->cols == 0) return '';
			$row = '<TR class="head" valign="top">';
			if($small)
			{
				$row.= '<TD colspan="'.$this->cols.'" height="1">';
			}
			else
			{
				$row.= '<TD colspan="'.$this->cols.'" height="3">';
				$row.= '<a name="actual_races"></a>';
			}
			$row.= '</TD>';
			$row .= '</TR>';
			return $row;
		}

		//__________________________________________________________________
		protected function _get_pre_footer() {}	// inner class function
	}

//==================================================================
	class html_table_nfo extends html_table_base
	{	// information table
		protected $c1_width, $c2_width;

		//__________________________________________________________________
		function html_table_nfo($table_class_name = '')
		{
			html_table_base::html_table_base(($table_class_name != '') ? $table_class_name : 'ctnf');
			$this->cellpadding = 0;
			$this->cellspacing = 0;
			$this->c1_width = 150;
			$this->c2_width = 20;
			$this->table_width = 80;
			$this->enable_row_select = false;
		}

		//__________________________________________________________________
		function get_new_row ($title,$value)
		{
			$rc =($this->highlighted_next_row) ? 'highlight' : 'r1';
			$row = '<TR class="r2" height="3"><TD colspan="3"></TD></TR>'.
			'<TR class="'.$rc.'" valign="top">'.
			'<TD class="'.$this->class_name.ALIGN_RIGHT.'" width="'.$this->c1_width.'"><B>'.$title.'</B></TD>'.
			'<TD width="'.$this->c2_width.'"></TD>'.
			'<TD class="'.$this->class_name.ALIGN_LEFT.'">'.$value.'</TD>'.
			'</TR>';
			$this->highlighted_next_row = false;
			return $row;
		}

		//__________________________________________________________________
		protected function _get_pre_footer()
		{	// inner class function
			$row = '<TR class="r2" height="3"><TD colspan="3"></TD></TR>';
			return $row;
		}
	}

//==================================================================
	class html_table_form extends html_table_base
	{	// form table
		protected $c1_width, $c2_width;
		var $enable_row_bgcolor;

		//__________________________________________________________________
		function html_table_form($table_class_name = '')
		{
			html_table_base::html_table_base(($table_class_name != '') ? $table_class_name : 'ctfo');
			$this->cellpadding = 0;
			$this->cellspacing = 0;
			$this->c1_width = 150;//'30%';
			$this->c2_width = 20;
			$this->table_width = 90;
			$this->enable_row_select = false;
			$this->enable_row_bgcolor = false;
			$this->paddingLR = 4;	// left, right
			$this->paddingTB = 3;	// top, bottom
			
//			$this->enable_row_bgcolor = true;	// debug
			$this->bgc_row1 = '#aa3';	// debug
		}

		//__________________________________________________________________
		function get_new_row ($title,$value)
		{	// form information color of text in value 
			$row = '<TR';
			if ($this->enable_row_bgcolor)
				$row .= ' class="r1"';
			$row .= ' valign="top">'.
			'<TD class="'.$this->class_name.ALIGN_RIGHT.'" width="'.$this->c1_width.'"><B>'.$title.'</B></TD>'.
			'<TD width="'.$this->c2_width.'"></TD>'.
			'<TD class="'.$this->class_name.'form">'.$value.'</TD>'.
			'</TR>';
			return $row;
		}

		//__________________________________________________________________
		function get_new_row_text ($title,$value)
		{	// normal color of text in value
			$row = '<TR';
			if ($this->enable_row_bgcolor)
				$row .= ' class="r1"';
			$row .= ' valign="top">'.
			'<TD class="'.$this->class_name.ALIGN_RIGHT.'" width="'.$this->c1_width.'"><B>'.$title.'</B></TD>'.
			'<TD width="'.$this->c2_width.'"></TD>'.
			'<TD class="'.$this->class_name.ALIGN_LEFT.'">'.$value.'</TD>'.
			'</TR>';
			return $row;
		}

		//__________________________________________________________________
		function get_new_row_simple ($text)
		{
			$row = '<TR';
			if ($this->enable_row_bgcolor)
				$row .= ' class="r1"';
			$row .= ' valign="top">'.
			'<TD class="'.$this->class_name.ALIGN_CENTER.'" colspan="3">'.$text.'</TD>'.
			'</TR>';
			return $row;
		}

		//__________________________________________________________________
		function get_empty_row ($height = 3)
		{
			$row = '<TR';
			if ($this->enable_row_bgcolor)
				$row .= ' class="r1"';
			$row .= ' height="'.$height.'">'.
			'<TD colspan="3"></TD>'.
			'</TR>';
			return $row;
		}

		//__________________________________________________________________
		protected function _get_pre_footer() {}	// inner class function
	}

//==================================================================
}	// define (HTML_TABLE_CLASS_INCLUDED)
?>