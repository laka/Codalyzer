<?php
/*
	**************************************************************
	* codalyzer
	* -  Make sorted tables
	**************************************************************
	
	Needs a query, and an optional list of columndata:
*/

class orderedtable {

	private $totalsum		= 0;              // true or false, sums up the values (or averages etc) and prints the totals (default:sum)
	private $columndata;                      // array('dbcolumnname' => array(orderarray, Frontendname,  width,  summationtype, URL-prefix, compareto));	
	private $limit			= 10;             // rows per page/totally
	private $total			= 1;
	private $tablewidth		= '100%';         // the width of the table
	private $tableclass		= 'summary';      // CSS-class of the table
	private $totalclass		= 'sum';          // CSS-class of the sum (if enabled)
	private $order			= 'DESC';         // DESC or ASC
	private $firstprefix	= '&amp;';        // first prefix in the query string
	private $url;						      // base url of the page with the tables
	private $urlvars		= array('mode');  // variables from the url (to prevent it from occurring twice in the url) (should be done automatically)
	private $currentpage	= 1;              // current page
	private $orderby, $query, $sortable, $result, $tableheaders, $db, $lang;
	
	static protected $urlprefix;              // handles the prefixes (if more than one sortable table is printed)
	static protected $numtables = 0;          // counts the number of tables created..
	
	public function __construct($query, $sortable = 0) {
        global $lang;
        $this->lang = $lang;    
    
		$this->db = database::getInstance();  // makes us a database object	
		$this->assignLetter ();
		
		// should do some validation here
		$this->sortable = $sortable;
		$this->query = $query;
	}
	
	// Set-functions
	// setWidth - sets the width of the table
	public function setWidth($width){ $this->tablewidth = $width; }
	// setClass - sets the class of the table
	public function setClass($class){ $this->tableclass = $class; }	
	// setTotalClass - sets the class of the last row, if enabled
	public function setTotalclass($class){ $this->totalclass = $class; }	
	// setTotalSum - enables/disbles the last row
	public function setTotalSum($totalsum){ $this->totalsum = $totalsum; }		
	// setLimit - sets the number of rows per page 
	public function setLimit($limit){ $this->limit = $limit; }		
	// setOrderBy - controls what to order by
	public function setOrderBy($orderby){ $this->orderby = $orderby; }	
	// setOrder - sets which order to order in
	public function setOrder($order){ $this->order = $order; }		
	// setUrl - sets the base url for the table
	public function setUrl($url) { $this->url = $url; }
	// setUrlVars - stores the variables already in the url (should probably be done automatically)
	public function setUrlVars($urlVars) { $this->urlvars = $urlVars; }	
	// setTotalRows - sets the number of rows totally
	public function setTotalRows($total){ $this->total = $total; }		
	// setColumnData - set detailed information on the columns
	public function setColumnData($columndata){ $this->columndata = $columndata; }
	
	// assigns a unique letter to this instance of the class
	protected function assignLetter (){
		// we asign a letter to each table, so it can have its own order-URLs
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUV';
		self::$urlprefix = $alphabet{self::$numtables};
		self::$numtables++;			
	}
	
	// Simply returns us the opposite of $this->order
	private function oppositeOrder (){
		if($this->order == 'DESC'){
			return 'ASC';
		} else {
			return 'DESC';
		}
	}
	
	// Counts the number of pages
	private function numPages (){
		return ceil($this->total/$this->limit);
	}
	
	// fetches all elements from the URL that haven't anything to do with this table, returns them as a query string
	private function otherElements(){
		$notours = array();
		foreach($_GET as $var => $value){
			if(!ereg("^".self::$urlprefix."[aop]$", $var) && !in_array($var, $this->urlvars)){
				$notours[$var] = $value;
			}
		}
		$i = 0;
		foreach($notours as $var=>$value){
			if($i == 0){
				$querystring  = $this->firstprefix . $var . '=' . $value;
			} else {
				$querystring .= '&amp;' . $var . '=' . $value;			
			}
			$i++;
		}
		return $querystring;
	}
	
	// generates a url based on url, what to order by, which order to order, and keeps all other variables from the query string
	public function urlGenerator ($page){
		$urlbase = $this->url.$this->otherElements()."&".self::$urlprefix."o={$this->orderby}&".self::$urlprefix."a={$this->order}&".self::$urlprefix."p=$page";
		return $urlbase;
	}	
	
	// Generates HTML-links for the headers of sortable tables
	private function orderLink ($column){
		// makes sure we've got the right page
		$this->currentPage();
		// if we are ordering by that certain column, we make a link to sort the other way
		if($this->orderby == $column){
			return "<img src=\"img/".$this->oppositeOrder().".gif\"> <a href=\"".$this->url.$this->otherElements()."&amp;".self::$urlprefix."o=$column&amp;".self::$urlprefix."a=".$this->oppositeOrder()."&amp;".self::$urlprefix."p=".$this->currentpage."\">";		
		}
		// if not, we just print out a link to the default order
		else{
			if(count($this->columndata) > 0){
				$defaultorder = current($this->columndata[$column][0]);
			} else {
				$defaultorder = 'DESC';
			}
			return "<img src=\"img/none.gif\"> <a href=\"".$this->url.$this->otherElements()."&amp;".self::$urlprefix."o=$column&amp;".self::$urlprefix."a=$defaultorder&amp;".self::$urlprefix."p=".$this->currentpage."\">";			
		}
	}

	// finds out which page we're really on...
	private function currentPage (){
		$this->currentpage = (is_numeric($_GET[self::$urlprefix.'p'])) ? $_GET[self::$urlprefix.'p'] : 1;  
		if(!is_numeric($this->currentpage) || $this->currentpage < 1 || $this->currentpage > $this->numPages()){
			$this->currentpage = 1;
		} else {
			$this->currentpage = round($this->currentpage);
		}
	}
	
	// finds the headers of a table, if not specified
	private function tableHeaders (){
        // creates an empty array so in_array doesn't give a warning later in the script
        $this->tableheaders = array();
		if(count($this->tableheaders) == 0 && count($this->columndata) == 0){
			$this->result = database::getInstance()->sqlResult($this->query);
			$firstrow = mysql_fetch_assoc($this->result);
			$this->tableheaders = array_keys($firstrow);		
		}
	}

	// takes care of the SQL-query
	private function createQuery (){
		$orders = array('ASC', 'DESC');
        $from = $this->limit*$this->currentpage-$this->limit;
        if(strlen($this->orderby) > 0){
            $this->query .= ' ORDER BY ';
            // hvis det er spesifisert spesielt hvordan den skal sorteres...
            if($this->columndata[$this->orderby][0]){
                // if the list currently is ordered opposite of default...
                $defaultorder = current($this->columndata[$this->orderby][0]);
                if($orders[$defaultorder] != $this->order){
                    $opposite = TRUE;
                }
                foreach($this->columndata[$this->orderby][0] as $column => $order){
                    $t = ($opposite) ? $orders[!$order] : $orders[$order];
                    $ordersql[] = " $column $t ";
                }
                $this->query .= implode(',', $ordersql);
            }
            // columndata is not specified. we add order by and order, if anything is specified
            else {
                if(strlen($this->orderby) > 0 && strlen($this->order) > 0){
                    $this->query .= " {$this->orderby} {$this->order}";
                }
            }
        }
		if($this->limit){
			$this->currentPage ();
			$from = $this->limit*$this->currentpage-$this->limit;
			$this->query .= " LIMIT $from, {$this->limit}";
		}
		
	}
	
	// prints out the table headers
	private function printHeaders (){
		echo "\t<tr>\n";
			if($this->columndata){
				foreach($this->columndata as $c => $columninfo){
					$name = $columninfo[1];
					if($this->sortable){
						$name = $this->orderLink($c) . $name;
					}
					$width = $columninfo[2];
					echo "\t\t<th width=\"$width\">$name</th>\n";
				}
			} else { 
				$width = round(100/(count($this->tableheaders)));
				foreach($this->tableheaders as $headername){
					if($this->sortable){
						$headername = $this->orderLink($headername) . $headername;
					}
					echo "\t\t<th width=\"$width%\">$headername</th>\n";
				}
			}
		echo "\t</tr>\n";
	}

	// compares two numbers, and returns a HTML-tag, containing a picture of the conclusion...
	private function compare ($current, $prev){
		$diff = $current-$prev;
		if($diff > 0)
			$change = 'up';
		elseif($diff < 0)
			$change = 'down';
		elseif(($diff == $current) || ($diff == 0))
			$change = 'statusquo';
		return "<img src=\"img/$change.gif\" class=\"change\" alt=\"$change\"> ";
	}	
	
	// prints out a navigation bar
	public function pageSelector (){
        // finds out what "page" is in the chosen language...
        global $lang;
        
		// fetches the current page number... SHOULD be done in the constructor, but then it wouldn't happen in resultlist
		$this->currentPage();
		$pageselector .= "<table class=\"summary\" width=\"100%\"><tr><th width=\"50%\">" . $lang['m_page'] . ":</th><td>";
		for($i = 1; $i<=$this->numPages (); $i++){
			if($this->currentpage == $i){
				$pageselector .= "<strong>$i</strong>  ";
			}
			else{
				$pageselector .= "<a href=\"".$this->urlGenerator ($i)."\">$i</a>  ";
			}
		}
		$pageselector .= "</td></tr></table>";
		return $pageselector;
	}

	// prints out the table itself
	public function printTable (){
		// fetch tableheaders, if not specified in $this->columndata
		$this->tableHeaders ();				
        
		// validata $_GET-input, if the column exists, we order by it.
		if(isset($_GET[self::$urlprefix.'o']) && (in_array($_GET[self::$urlprefix.'o'], $this->tableheaders) || count($this->columndata[$_GET[self::$urlprefix.'o']]) > 0) ){
            $this->orderby	= $_GET[self::$urlprefix.'o'];
		}
		
		// if order = asc or desc, order=order, else: order=desc.
		$this->order	= ($_GET[self::$urlprefix.'a'] == 'ASC' || $_GET[self::$urlprefix.'a'] == 'DESC') ? $_GET[self::$urlprefix.'a'] : 'DESC' ; 
	
		// we create the SQL-query
		$this->createQuery ();
		$result = database::getInstance()->sqlResult($this->query);

		// loops through the rows
        if($result){
            // prints out the table...
            echo "<table class=\"{$this->tableclass}\" width=\"{$this->tablewidth}\">\n";
            $this->printHeaders ();
            $rownumber = 0;
            
            // decides whether to use MYSQL_ASSOC or MYSQL_NUM. Uses the second if column names/order is not specified.
            if(count($this->columndata) > 0){
                $fetchtype = MYSQL_ASSOC;
            } else {
                $fetchtype = MYSQL_NUM;
            }        
        
            while($row = mysql_fetch_array($result, $fetchtype)){
            echo "\t<tr>\n";		
                if(count($this->columndata) > 0){
                    // loops through the columns
                    foreach($this->columndata as $header=>$d){
                        echo "\t\t<td>";
                        
                        if(isset($d[5])){
                            echo $this->compare($row[$header], $row[$d[5]]);
                        }
                        if(strlen($d[4]) > 0){
                            // if something in the url pattern points to another column (enclosed by *), we find it..
                            preg_match('/\*([A-Za-z0-9_.-]*)\*/', $d[4], $matches);
                            if(count($matches) > 0){
                                foreach($matches as $match){
                                    if(isset($row[$match])){
                                        $d[4] = str_replace("*$match*", urlencode($row[$match]), $d[4]);
                                        $replaced = TRUE;
                                    }
                                }
                            }
                            // if we find something, the value of the column won't be appended to the end, unless that is specified with *
                            if(!$replaced)
                                $urlize = urlencode($row[$header]);
                            else 
                                $urlize = '';
                            $row[$header] = "<a href=\"{$d[4]}$urlize\">{$row[$header]}</a>";
                        }					
                        echo $row[$header];
                        echo "</td>\n";   
                        
                        // if we want to print out the sum...
                        if($this->totalsum){
                            switch ($this->columndata[$header][3]){
                                case 'avg':
                                    $totals[$header] = round(($totals[$header]*$rownumber+$row[$header])/(++$rownumber),2);          
                                break;					
                                
                                case 'totalstring':
                                $totals[$header] = 'SUM:';
                                break;	
                                
                                default:
                                case 'sum':
                                    if(is_numeric($row[$header]))
                                        $totals[$header] += $row[$header];
                                    else 
                                        $totals[$header] = '';
                                break;					
                            }
                            // if it is compared to something
                            if(isset($this->columndata[$header][5])){
                                if(strlen($row[$this->columndata[$header][5]]) > 0)
                                    $comparesum[$header] += $row[$header]-($row[$this->columndata[$header][5]]);
                            }
                        }
                    }				
                } else {
                    for($i=0; $i<count($row); $i++){
                        // if we want to print out the sum...
                        if($this->totalsum){
                            $totals[$header] += $row[$header];
                        }				
                        echo "\t\t<td>";
                            if(is_numeric($row[$i])){
                                echo round($row[$i], 2);                            
                            } else {
                                echo $row[$i];   
                            }
                        echo "</td>\n";
                    }
                }
                echo "\t</tr>\n";	
            }
            if($this->totalsum && is_array($totals)){
                echo "\t<tr>\n";
                foreach($totals as $header=>$sum){
                    if(isset($this->columndata[$header][5])){
                        echo "\t\t<td class=\"{$this->totalclass}\">";
                        echo $this->compare($comparesum[$header], 0);
                        echo "$sum</td>\n";
                    } else {
                        echo "\t\t<td class=\"{$this->totalclass}\">$sum</td>\n";
                    }
                }			
                echo "\t</tr>\n";	
            }
            echo "</table>";
        } else {
            echo "<p><strong>Error: </strong>Could not get data</p>";
        }
	}
}
?>