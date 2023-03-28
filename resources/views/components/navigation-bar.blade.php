<ul v-if="token && user"
	class="navbar-nav bg-dark sidebar sidebar-dark accordion toggledx border-right-secondary"
	id="accordionSidebar">

	<a class="sidebar-brand d-flex align-items-center justify-content-center p-0" href="{{ url('/') }}">
		<div class="sidebar-brand-icon ">
			<img src="{{ asset('images/logo.jpeg') }}" alt="logo-tecnplus" srcset="" width="100%">

		</div>
		{{-- <strong class="h3 text-bold text-dark">Tecno</strong><strong class="h3 text-bold text-white">plus</strong> --}}
	</a>

	<hr class="sidebar-divider" />
	<!-- Nav Item - Dashboard -->

	<li class="nav-item active">
		<a class="nav-link" href="#">
			<i class="bi bi-person-square"></i>
			<span>@{{ user.name }}</span>
		</a>
	</li>

	<hr class="sidebar-divider" />

	<!-- Nav Item - Dashboard -->
	<div class="sidebar-heading">
		Tecnoplus
	</div>
	<li class="nav-item bg-primary">
		<router-link class="nav-link " active-class="active" to="/"><i
				class="bi bi-receipt"></i><span>Facturar</span>
		</router-link>
	</li>
	<li class="nav-item">
		<router-link class="nav-link " active-class="active" to="/checker"><i
				class="bi bi-receipt"></i><span>Verificador</span>
		</router-link>
	</li>

	<!-- Nav Item - Shop Collapse Menu -->
	<li class="nav-item">
		<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseShop" aria-expanded="true"
			aria-controls="collapseShop">
			<i class="bi bi-shop"></i>
			<span>Almacén</span>
		</a>
		<div id="collapseShop" class="collapse" aria-labelledby="headingShop" data-parent="#accordionSidebar">
			<div class="bg-white py-2 collapse-inner rounded">
				<h6 class="collapse-header">Almacén:</h6>
				<router-link v-if="validatePermission('product.index')" class="collapse-item " to="/products"
					v-if="validatePermission('product.index')">Productos
				</router-link>
				<router-link v-if="validatePermission('product.index')" class="collapse-item " to="/stock"
					v-if="validatePermission('product.update')">Inventario
				</router-link>
				<router-link v-if="validatePermission('category.index')" class="collapse-item " to="/categories"
					v-if="validatePermission('category.index')">Categorias
				</router-link>
				<router-link v-if="validatePermission('brand.index')" class="collapse-item " to="/brands">Marcas</router-link>
				<router-link v-if="validatePermission('tax.index')" class="collapse-item" to="/taxes"
					v-if="validatePermission('tax.index')">Impuestos</router-link>
			</div>
		</div>
	</li>
	<li class="nav-item" v-if="validatePermission('order.index')">
		<router-link class="nav-link " active-class="active" to="/orders"><i class="bi bi-receipt"></i><span>Ordenes</span>
		</router-link>
	</li>
	<li class="nav-item d-none" v-if="validatePermission('order.index')">
		<router-link class="nav-link " active-class="active" to="/create-edit-order-mobile/0"><i class="bi bi-receipt"></i><span>Crear orden </span>
		</router-link>
	</li>
	<li class="nav-item" v-if="validatePermission('credit.index')">
		<router-link class="nav-link " active-class="active" to="/credits"><i
				class="bi bi-receipt"></i><span>Créditos</span>
		</router-link>
	</li>
	<li class="nav-item" v-if="validatePermission('billing.index')">
		<router-link class="nav-link " active-class="active" to="/billings"><i
				class="bi bi-receipt"></i><span>Compras</span>
		</router-link>
	</li>


	<!-- Nav Item - Pages Collapse Menu -->
	<li class="nav-item">
		<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePeople" aria-expanded="true"
			aria-controls="collapsePeople">
			<i class="bi bi-people"></i>
			<span>Terceros</span>
		</a>
		<div id="collapsePeople" class="collapse" aria-labelledby="headingPeople" data-parent="#accordionSidebar">
			<div class="bg-white py-2 collapse-inner rounded">
				<h6 class="collapse-header">Personas:</h6>
				<router-link v-if="validatePermission('client.index')" class="collapse-item" to="/clients"
					v-if="validatePermission('client.index')">
					Clientes</router-link>
				<router-link v-if="validatePermission('supplier.index')" class="collapse-item" to="/suppliers"
					v-if="validatePermission('supplier.index')">
					Proveedores</router-link>
			</div>
		</div>
	</li>
	<li class="nav-item">
		<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport" aria-expanded="true"
			aria-controls="collapseReport">
			<i class="bi bi-shop"></i>
			<span>Informes</span>
		</a>
		<div id="collapseReport" class="collapse" aria-labelledby="headingReport" data-parent="#accordionSidebar">
			<div class="bg-white py-2 collapse-inner rounded">
				<h6 class="collapse-header">Informe:</h6>
				<router-link class="collapse-item " to="/reports/closing" v-if="validatePermission('product.index')">
					Informe cierre
				</router-link>
				<router-link class="collapse-item " to="/reports/report-sale" v-if="validatePermission('product.index')">Informe
					de ventas diarias
				</router-link>
				<router-link class="collapse-item " to="/reports/report-general-sales"
					v-if="validatePermission('product.index')">Informe
					general de ventas
				</router-link>
				<router-link class="collapse-item " to="/reports/report-product-sales"
					v-if="validatePermission('product.index')">Informe
					de ventas de productos
				</router-link>

			</div>
		</div>
	</li>

	<li class="nav-item">
		<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSettings" aria-expanded="true"
			aria-controls="collapseSettings">
			<i class="bi bi-sliders"></i>
			<span>Configuraciones</span>
		</a>
		<div id="collapseSettings" class="collapse" aria-labelledby="headingSettings" data-parent="#accordionSidebar">
			<div class="bg-white py-2 collapse-inner rounded">
				<h6 class="collapse-header">Sistema:</h6>
				<router-link class="collapse-item" to="/profile">Perfil</router-link>
				<router-link v-if="validatePermission('configuration')" class="collapse-item" to="/configuration">Configuración
					general</router-link>
				<router-link class="collapse-item" to="/roles" v-if="validatePermission('rol.index')">Roles</router-link>
				<router-link class="collapse-item" v-if="validatePermission('user.index')" to="/users">Usuarios
				</router-link>
				<router-link class="collapse-item" v-if="validatePermission('box.index')" to="/boxes">Cajas</router-link>
				<router-link class="collapse-item" v-if="validatePermission('zone.index')" to="/zones">Zonas</router-link>
				<router-link class="collapse-item" v-if="validatePermission('table.index')" to="/tables">Mesas</router-link>
			</div>
		</div>
	</li>

	<hr class="sidebar-divider d-none d-md-block">

	<li class="nav-item">
		<a class="nav-link" href="#" @click="logout">
			<i class="bi bi-box-arrow-right"></i>
			<span>Cerrar Sesión</span></a>
	</li>

	<hr class="sidebar-divider d-none d-md-block">

	<div class="text-center d-none d-md-inline">
		<button class="rounded-circle border-0" id="sidebarToggle"><i class="bi bi-brightness-high"></i></button>
	</div>
</ul>
{{-- </div> --}}