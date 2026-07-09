/**
 * RIYAA'S COLLECTION — Configuration du frontend
 *
 * En local (XAMPP), l'URL de l'API est auto-détectée.
 * En production (Vercel), l'API est hébergée sur Hostinger :
 * https://riyaas.grosbit.com/api
 */
const IS_LOCAL = ['localhost', '127.0.0.1'].includes(location.hostname);

const CONFIG = {
    API_BASE: IS_LOCAL
        ? 'http://localhost/riyaas-collection/backend/api'
        : 'https://riyaas.grosbit.com/api', // API PHP sur Hostinger

    WHATSAPP_NUMBER: '22890128638',
    TMONEY_NUMBER: '70 92 66 76',
    DEPOSIT_PERCENT: 50,
    DELIVERY_DAYS: 5,
    CURRENCY: 'FCFA',
};
