<?php
/**
 * views/profile/show.php
 *
 * NOTA DE INTEGRACIÓN:
 * Esta vista asume que el proyecto tiene un layout base (header/footer)
 * que se incluye desde el Controller (por ejemplo con $this->render()
 * envolviendo esta vista en views/layout.php). Ajusta las rutas de
 * include si el layout se llama distinto.
 *
 * Variable disponible: $user => ['id' => ..., 'name' => ..., 'email' => ...]
 */
?>

<div class="profile-page">
    <h1>Mi perfil</h1>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- ===================== Datos generales ===================== -->
    <section class="profile-card">
        <h2>Datos generales</h2>
        <form action="/profile/name" method="POST">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                <small>El correo no se puede modificar desde aquí.</small>
            </div>

            <div class="form-group">
                <label for="name">Nombre</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($user['name']) ?>"
                    maxlength="100"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">Guardar nombre</button>
        </form>
    </section>

    <!-- ===================== Cambiar contraseña ===================== -->
    <section class="profile-card">
        <h2>Cambiar contraseña</h2>
        <form action="/profile/password" method="POST">
            <div class="form-group">
                <label for="current_password">Contraseña actual</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">Nueva contraseña</label>
                <input type="password" id="new_password" name="new_password" minlength="8" required>
                <small>Mínimo 8 caracteres.</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar nueva contraseña</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
            </div>

            <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
        </form>
    </section>
</div>
