<?php
class mikran_ModelSaleOrder extends ModelSaleOrder {
    public function getTotalOrdersByCustomerId($customer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}
}