 <?php
class PC_paging {
	protected $page, $total, $perPage, $limit, $offset, $initialOffset, $cutout;
	const PP_DEFAULT = 30;
	public function __construct($page, $perPage=null) {
		$this->page = (int)$page;
		if ($this->page < 1) $this->page = 1;
		$this->perPage = (int)$perPage;
		if ($this->perPage < 1) $this->perPage = self::PP_DEFAULT;
		$this->limit = $this->perPage;
		$this->offset = $this->page * $this->perPage - $this->perPage;
	}
	public function Set_total($total) {
		$this->total = (int)$total;
		$this->totalPages = ceil($this->total / $this->perPage);
	}
	public function Get_total() {
		return (int)$this->total;
	}
	public function Get_offset() {
		$offset = $this->offset;
		if ($this->initialOffset > 0) $offset += $this->initialOffset;
		return $offset;
	}
	public function Get_limit() {
		$limit = $this->limit;
		if ($this->cutout > 0) $limit = $limit - $this->cutout;
		return $limit;
	}
	public function Set() {}
	public function Get() {}
	public function Next() {
		$this->page++;
		return true;
	}
	public function Previous() {
		if ($this->page <= 1) return false;
		$this->page--;
		return true;
	}
	public function Set_initial_offset($count=0) {
		$this->initialOffset = (int)$count;
	}
	public function Set_cutout($count=0) {
		$this->cutout = (int)$count;
	}
}