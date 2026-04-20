START TRANSACTION;

CREATE TABLE IF NOT EXISTS inventory_supplier_sources (
    source_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    inventory_id INT NOT NULL,
    supplier_id INT NOT NULL,
    unit_cost DECIMAL(10,2) DEFAULT NULL,
    currency_code CHAR(3) NOT NULL DEFAULT 'LKR',
    min_order_qty INT DEFAULT NULL,
    lead_time_days INT DEFAULT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    first_seen_date DATE DEFAULT NULL,
    last_purchase_date DATE DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (source_id),
    UNIQUE KEY uk_inventory_supplier (inventory_id, supplier_id),
    KEY idx_iss_supplier_active (supplier_id, is_active),
    KEY idx_iss_inventory_active (inventory_id, is_active),
    CONSTRAINT fk_iss_inventory
        FOREIGN KEY (inventory_id) REFERENCES inventory (inventory_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_iss_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers (supplier_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO inventory_supplier_sources (
    inventory_id,
    supplier_id,
    unit_cost,
    is_primary,
    is_active,
    first_seen_date,
    last_purchase_date
)
SELECT
    i.inventory_id,
    i.supplier_id,
    i.unit_cost,
    1,
    1,
    CURDATE(),
    CURDATE()
FROM inventory i
WHERE i.supplier_id IS NOT NULL
ON DUPLICATE KEY UPDATE
    unit_cost = COALESCE(VALUES(unit_cost), inventory_supplier_sources.unit_cost),
    is_primary = GREATEST(inventory_supplier_sources.is_primary, VALUES(is_primary)),
    is_active = 1,
    updated_at = CURRENT_TIMESTAMP;

INSERT INTO inventory_supplier_sources (
    inventory_id,
    supplier_id,
    unit_cost,
    is_primary,
    is_active,
    first_seen_date,
    last_purchase_date
)
SELECT
    sp.inventory_id,
    sp.supplier_id,
    MAX(sp.unit_cost),
    0,
    1,
    MIN(sp.purchase_date),
    MAX(sp.purchase_date)
FROM stock_purchases sp
GROUP BY sp.inventory_id, sp.supplier_id
ON DUPLICATE KEY UPDATE
    unit_cost = COALESCE(VALUES(unit_cost), inventory_supplier_sources.unit_cost),
    first_seen_date = COALESCE(inventory_supplier_sources.first_seen_date, VALUES(first_seen_date)),
    last_purchase_date = GREATEST(
        COALESCE(inventory_supplier_sources.last_purchase_date, '1000-01-01'),
        COALESCE(VALUES(last_purchase_date), '1000-01-01')
    ),
    is_active = 1,
    updated_at = CURRENT_TIMESTAMP;

COMMIT;