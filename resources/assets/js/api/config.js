const apiDomain = Laravel.apiDomain;
export const siteName = Laravel.siteName;

export const generalData = apiDomain + '/general-data'
export const login = apiDomain + '/authenticate';
export const forgot = apiDomain + '/forgot-password';
export const currentUser = apiDomain + '/user';
export const avisosPendientes = apiDomain + '/avisos-clientes/pendientes'
export const cantAvisosPendientes = apiDomain + '/avisos-clientes/pendientes/cant'
export const vencimientos = apiDomain + '/avisos-clientes/vencimientos'

export const updateUserProfile = apiDomain + '/user/profile/update';
export const updateUserPassword = apiDomain + '/user/password/update';

export const rolesPermisos = apiDomain + '/roles/full';

export const urlUsuarios = apiDomain + '/usuarios/';
export const urlCombos = apiDomain + '/combos/';