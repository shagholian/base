<?php
namespace packages\base\views\traits;
trait listTrait{
	protected $dataList = array();
	protected $currentPage;
	protected $totalPages;
	protected $itemsPage;
	protected $totalItems;
	public function setDataList($data){
		$this->dataList = $data;
	}
	public function setPaginate($currentPage, $totalItems, $itemsPage){
		$this->currentPage = $currentPage;
		$this->totalItems = $totalItems;
		$this->itemsPage = $itemsPage;
	}
}
