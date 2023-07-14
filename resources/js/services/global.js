const api = 'http://192.168.1.80/restaurante/public/api';


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

    var search = permissions.filter((filtro) => {
      return filtro.name.match(permission);
    });

    return search.length > 0 ? true : false;
  }
};