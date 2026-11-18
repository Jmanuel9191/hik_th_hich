<?php

/**
 * Editar usuario: usa el mismo formulario que crear (_form.php), sin el campo contraseña.
 *
 * @var yii\web\View $this
 * @var app\models\Usuarios $model
 * @var array $roles
 */

echo $this->render('_form', ['model' => $model, 'roles' => $roles]);
