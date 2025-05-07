USE ch015015b1_db_webmax;

SELECT bits.*, punkt.name as namePunkt, material.name as nameMater  
FROM bits   
  LEFT JOIN material ON bits.id_M = material.id_M
  LEFT JOIN punkt ON  punkt.id_P = bits.id_P
ORDER BY bits.id_B