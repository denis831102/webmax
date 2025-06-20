use ch015015b1_db_webmax;

SELECT users.id as id_U, users.PIB,	
  punkt.id_P as id_P, punkt.name as name_P,
  matKategories.id_K as id_K, matKategories.name as name_K,
  material.id_M as id_M, material.name as name_M, material.unit,
  -- sum(bits.count)as sumCount 
  bits.count as sumCount
FROM users 
  LEFT JOIN status ON users.status = status.id
  LEFT JOIN punkt ON users.id = punkt.id_U
  LEFT JOIN bits ON punkt.id_P = bits.id_P
  LEFT JOIN material ON bits.id_M = material.id_M
  LEFT JOIN matKategories ON material.id_K = matKategories.id_K
WHERE users.id IN (30) -- AND matKategories.id_G <> 1
-- GROUP BY id_U, id_K, id_M
ORDER BY id_U, id_M