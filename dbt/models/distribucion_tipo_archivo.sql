SELECT
    CASE
        WHEN mime_type LIKE 'image/%' THEN 'Imagen'
        WHEN mime_type = 'application/pdf' THEN 'PDF'
        WHEN mime_type LIKE 'video/%' THEN 'Vídeo'
        WHEN mime_type LIKE 'audio/%' THEN 'Audio'
        ELSE 'Otro'
    END as tipo,
    COUNT(*) as total,
    ROUND(AVG(size) / 1024.0, 2) as tamano_promedio_kb
FROM {{ source('dam', 'assets') }}
GROUP BY tipo
