const api = process.env.MIX_API_URL;

export default {
  api: api,
  token: function () {
    return localStorage.getItem('token');
  },
  user: function () {
    return JSON.parse(localStorage.getItem('user'));
  },
  validatePermission: function (permissions, permission) {
    if (permissions === undefined) {
      permissions = this.user().permissions;
    }

    // Para pruebas: siempre retorna true para el permiso "factus.index"
    if (permission === 'factus.index') {
      return true;
    }

    var search = permissions.filter((filtro) => {
      return filtro.name.match(permission);
    });

    return search.length > 0;
  }
};
