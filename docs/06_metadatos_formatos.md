# Metadatos y Formatos

Esta sección establece el marco técnico de información (metadatos) requeridos por el sistema para garantizar trazabilidad, descubrimiento en buscadores (SEO) y control de procesos.

## 1. Modelo de Metadatos

### Metadatos de Producto
Son los atributos que describen la naturaleza física y comercial del bien ofertado.
- `_sku`: Identificador interno único del producto.
- `_price` / `_regular_price` / `_sale_price`: Valores de costo comercial.
- `_stock_status`: Estado actual (instock, outofstock, onbackorder).
- `_manage_stock`: Booleano (sí/no) para el rastreo automatizado.
- `_weight`, `_length`, `_width`, `_height`: Variables numéricas logísticas.
- **Atributos Personalizados:** `marca` (string), `material` (string), `condicion` (nuevo/usado).

### Metadatos de Proceso
Datos utilizados por el sistema para controlar el estado del flujo de venta y auditorías operativas.
- `_vendor_id`: ID del usuario/tienda creadora del producto (Crucial para el reparto de comisiones).
- `_commission_status`: Estado del pago de comisión al vendedor (unpaid, pending, paid).
- `_order_status`: Control logístico de la orden (wc-processing, wc-completed, wc-refunded).
- `_wcfm_review_status`: Notas o banderas colocadas por el Administrador durante las aprobaciones de catálogo.

### Metadatos de Contexto
Datos externos o ambientales que dan sentido analítico a las entidades.
- `_created_at` / `_modified_at`: Fechas exactas del ciclo de vida del producto.
- `_customer_ip_address`: IP del comprador (prevención de fraude).
- `_customer_user_agent`: Navegador y dispositivo del cliente.
- `_campaign_source` / `_utm_source`: Origen del tráfico de marketing que generó la venta.

## 2. Formatos a Gestionar por Rol

Para mantener el estándar en el sistema, existen diferentes formatos estructurados que cada rol debe usar:

### Administrador
- **Formatos de Reporte:** Exportación en **CSV o Excel (.xlsx)** para análisis financiero externo (reportes de ventas totales mensuales, retenciones de impuestos, pagos a vendedores pendientes).
- **Formatos de Auditoría:** Registro de actividad del sistema (logs de errores y aprobaciones) almacenados en `.txt` o sistemas de logs de WordPress.

### Vendedor (Vendor)
- **Importación/Exportación de Catálogo:**
  - Formato **CSV estándar de WooCommerce** para subir inventarios masivamente sin tener que hacerlo uno por uno en la interfaz.
  - Reglas del CSV: El archivo debe estar codificado en `UTF-8` para evitar errores de caracteres especiales en español (ñ, tildes). Los precios deben ir sin separadores de miles y el separador de decimales debe ser un punto (`.`).
- **Imágenes:**
  - Formato: **JPEG, WebP o PNG**.
  - Restricción: No mayor a 1 MB por imagen para no comprometer los tiempos de carga (Web Performance), con dimensiones recomendadas de `1000x1000` px.

### Cliente
- **Archivos Descargables (si aplica para productos digitales):** Formatos seguros estándar como **PDF**, **ZIP** o **MP4**, entregados a través de enlaces encriptados generados por el sistema tras el pago completado.
- **Recibos/Facturas:** Documentos generados por la plataforma en formato **PDF**, accesibles en el panel de su cuenta y enviados por correo.
