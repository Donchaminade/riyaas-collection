/**
 * RIYAA'S COLLECTION — Panier côté client (localStorage)
 *
 * Le panier est stocké localement ; les prix sont TOUJOURS revalidés
 * côté serveur (cart.php?action=validate) avant toute commande.
 */
const CART_KEY = 'riyaas_cart';

const Cart = {

    getItems() {
        try {
            return JSON.parse(localStorage.getItem(CART_KEY)) || [];
        } catch {
            return [];
        }
    },

    save(items) {
        localStorage.setItem(CART_KEY, JSON.stringify(items));
        Cart.updateBadge();
    },

    // item : objet retourné par cart.php?action=add
    // La couleur fait partie de la clé : deux couleurs = deux lignes distinctes
    add(item) {
        const items = Cart.getItems();
        const key   = `${item.product_id}_${item.variant_id || 0}_${item.color || ''}`;
        const found = items.find(i => `${i.product_id}_${i.variant_id || 0}_${i.color || ''}` === key);

        if (found) {
            found.quantity += item.quantity;
        } else {
            items.push(item);
        }
        Cart.save(items);
    },

    setQuantity(index, quantity) {
        const items = Cart.getItems();
        if (!items[index]) return;
        items[index].quantity = Math.max(1, quantity);
        Cart.save(items);
    },

    remove(index) {
        const items = Cart.getItems();
        items.splice(index, 1);
        Cart.save(items);
    },

    clear() {
        localStorage.removeItem(CART_KEY);
        Cart.updateBadge();
    },

    count() {
        return Cart.getItems().reduce((sum, i) => sum + i.quantity, 0);
    },

    total() {
        return Cart.getItems().reduce((sum, i) => sum + i.unit_price * i.quantity, 0);
    },

    updateBadge() {
        const badge = document.querySelector('.cart-badge');
        const count = Cart.count();
        if (!badge) return;
        badge.textContent   = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    },
};
