# Guía de Usuario: Administrador

Esta guía está dirigida al personal responsable de operar y gobernar el Marketplace desde la vista de administración general.

## 1. Acceso al Panel de Administración

1. Ingresa a `tusitio.com/wp-admin` (o la URL de acceso definida).
2. Utiliza las credenciales con perfil de Administrador.

## 2. Gestión de Vendedores (Vendors)

### Aprobación de Nuevos Vendedores
Cuando un nuevo usuario se registra solicitando ser vendedor, su estado suele ser "Pendiente".
1. Dirígete al menú de **WCFM** o al gestor de vendedores en el panel izquierdo.
2. Accede a la pestaña **Solicitudes de Vendedores**.
3. Revisa la documentación o perfil proporcionado.
4. Cambia el estado a **Aprobado** (o rechazado según corresponda).

### Suspensión de Vendedores
En caso de fraude o incumplimiento de términos:
1. Ve a la lista de Vendedores activos.
2. Selecciona el vendedor infractor y cambia su estado a **Suspendido**. Esto ocultará temporalmente sus productos del catálogo.

## 3. Configuración de Comisiones (Admin to Vendor)

El modelo de negocio se basa en la retención de un porcentaje o cuota fija por venta.

1. Ve a **WCFM -> Ajustes -> Comisiones**.
2. **Tipo de Comisión:** Puedes definir comisiones por porcentaje, fijas o una combinación de ambas.
3. **Comisiones Específicas:** Si hay negociaciones particulares, puedes ir al perfil de un vendedor específico y aplicar una regla de comisión que sobrescriba la global.

## 4. Retiros de Fondos (Withdrawals)

Los vendedores solicitarán el retiro de sus ganancias tras completar las ventas.
1. Ve a **WCFM -> Solicitudes de Retiro**.
2. Revisa las solicitudes pendientes. El sistema mostrará el saldo disponible (ventas totales menos comisiones y reembolsos).
3. Una vez que hayas realizado la transferencia bancaria (u otro método externo), aprueba la solicitud en el sistema para que se descuente del saldo virtual del vendedor.

## 5. Control de Calidad de Productos

Si la plataforma está configurada para revisar productos antes de publicarse:
1. Dirígete a la sección **Productos** (en WooCommerce o en WCFM).
2. Filtra por estado **Pendiente de Revisión**.
3. Verifica la calidad de las imágenes, títulos y descripciones (consulta la *Guía de Creación de Producto*).
4. Cambia el estado a **Publicado** si cumple con las normas, o devuelve a **Borrador** notificando al vendedor qué debe corregir.
