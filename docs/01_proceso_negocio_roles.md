# Proceso de Negocio y Definición de Roles

## Contexto del Proyecto

El proyecto es un Marketplace Multivendedor que permite a múltiples vendedores independientes crear y administrar sus propias tiendas dentro de la plataforma principal. La plataforma actúa como el facilitador central de transacciones, garantizando la seguridad, calidad y experiencia del usuario, al tiempo que proporciona herramientas para que cada vendedor escale sus ventas.

La arquitectura se apoya en **WooCommerce** (para el motor de comercio electrónico) y **WCFM (WooCommerce Frontend Manager)** (para la gestión de vendedores y comisiones).

## Flujo del Proceso de Negocio Core

1. **Onboarding de Vendedores:**
   - Un usuario solicita convertirse en vendedor (Vendor).
   - El Administrador (Admin) revisa la solicitud y la aprueba o rechaza.
   - Una vez aprobado, el vendedor configura su tienda (logo, banner, políticas, información de pago).

2. **Gestión de Catálogo:**
   - El Vendedor crea productos en su panel (dashboard).
   - Dependiendo de la configuración del Administrador, los productos pueden publicarse automáticamente o requerir aprobación previa.

3. **Ciclo de Compra:**
   - El Cliente (Customer) navega por el marketplace, añade productos al carrito (incluso de múltiples vendedores) y realiza el pago.
   - La plataforma procesa el pago total.
   - El sistema divide el pedido en sub-pedidos por vendedor y distribuye las notificaciones correspondientes.

4. **Gestión Logística y Cierre:**
   - Cada vendedor prepara su parte del pedido y actualiza el estado a "Enviado/Completado".
   - El Cliente recibe las notificaciones de seguimiento.
   - El Administrador gestiona el pago de comisiones y retiros a los vendedores según los acuerdos establecidos.

---

## Definición de Roles del Sistema

### 1. Administrador Global (Admin)

El administrador es el propietario de la plataforma. Su responsabilidad principal no es vender productos directamente, sino **gobernar** el ecosistema del marketplace.

- **Responsabilidades principales:**
  - Configuración general de la plataforma y métodos de pago/envío.
  - Aprobación, suspensión o eliminación de vendedores.
  - Gestión de estructuras de comisiones y procesamientos de retiros de fondos de los vendedores.
  - Aprobación de productos (si el control de calidad lo requiere).
  - Intervención en disputas entre clientes y vendedores.

### 2. Vendedor / Tienda (Vendor)

El vendedor es un usuario de negocio que utiliza la plataforma para comercializar sus productos. Utiliza el WCFM Dashboard (panel frontal) para no interactuar con el panel de administración interno (wp-admin).

- **Responsabilidades principales:**
  - Configuración de la información pública de su tienda.
  - Creación y mantenimiento de su catálogo de productos.
  - Gestión de su propio inventario.
  - Procesamiento y envío de sus pedidos.
  - Atención de consultas, reembolsos y devoluciones relacionadas con sus productos.

### 3. Cliente Final (Customer)

El cliente es el usuario final que adquiere bienes en la plataforma.

- **Responsabilidades principales:**
  - Mantenimiento de su perfil e información de facturación/envío.
  - Realización de compras.
  - Generación de reseñas y valoraciones para productos y vendedores.
