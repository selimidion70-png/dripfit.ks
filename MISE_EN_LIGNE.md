# Mettre DripFit.ks en ligne

Guide pas à pas pour publier le site sur un hébergement mutualisé (PHP + MySQL).
Compte environ 1 h la première fois.

---

## 0. Avant de commencer : ce qu'il faut acheter

**Un nom de domaine** — `.ks` n'existe pas sur Internet, il faut en choisir un autre :
`dripfitks.com`, `dripfit-ks.com`, `dripfitks.shop`… (~10-15 €/an, souvent offert la 1re année avec l'hébergement).

**Un hébergement mutualisé** (~3-10 €/mois). Vérifie que l'offre a :

- [ ] PHP **8.1 ou plus**
- [ ] MySQL ou MariaDB + **phpMyAdmin**
- [ ] **SSL gratuit** (Let's Encrypt) → le cadenas / https
- [ ] **Adresses email** sur ton domaine (pour envoyer les alertes de commande)
- [ ] Accès **FTP** ou gestionnaire de fichiers

---

## 1. Créer la base de données

1. Panneau de l'hébergeur → **Bases de données MySQL** → créer une base + un utilisateur.
2. **Note** les 4 infos : hôte (souvent `localhost`), nom de la base, utilisateur, mot de passe.
3. Ouvre **phpMyAdmin** → clique sur ta base à gauche → onglet **Importer**
   → choisis **`database/hebergement.sql`** → Exécuter.

> ⚠ Utilise bien `hebergement.sql`, pas `dripfit.sql` (qui contient `CREATE DATABASE dripfit`,
> refusé par les hébergeurs, et des produits d'exemple).

## 2. Créer l'adresse email d'envoi

Panneau → **Emails** → crée par exemple `porosi@tondomaine.com`.
Les alertes de commande partiront de cette adresse (une adresse Gmail comme expéditeur finirait en spam).

## 3. Envoyer les fichiers

Avec **FileZilla** (identifiants FTP dans le panneau) ou le **gestionnaire de fichiers**,
envoie dans le dossier du site (souvent `public_html/`) :

```
.htaccess          ← fichier caché : active l'affichage des fichiers cachés dans FileZilla
index.php
config/
controllers/
models/
views/
public/
logs/              ← avec son .htaccess
```

Pas besoin d'envoyer : `database/`, `MISE_EN_LIGNE.md` (ils sont de toute façon bloqués par le `.htaccess`).

Le dossier **`public/img/produits/`** doit être **modifiable** par PHP (photos envoyées depuis l'admin),
ainsi que **`logs/`** (journal des erreurs). Si l'envoi de photo échoue : clic droit → Permissions → `755`.

## 4. Créer `config/production.php` (sur le serveur seulement)

1. Sur le serveur, copie `config/production.exemple.php` → `config/production.php`.
2. Remplis : les 4 infos de la base (étape 1), l'email du cousin, l'adresse d'envoi (étape 2).

> Ne crée **pas** ce fichier sur ton PC : sans lui, Laragon continue de marcher normalement.
> Il contient le mot de passe de la base : ne l'envoie à personne.

## 5. Activer le https

Panneau → **SSL** → activer Let's Encrypt (gratuit) pour ton domaine. Attends quelques minutes.
Le site redirige ensuite tout seul de `http://` vers `https://` (grâce à `EN_PRODUCTION`).

> Si la page tourne en boucle (« trop de redirections ») : le SSL n'est pas encore actif.
> Mets temporairement `EN_PRODUCTION` sur `false` dans `production.php`, active le SSL, puis remets `true`.

## 6. Compte admin

1. Sur le site en ligne, clique **Regjistrohu** et crée le compte du cousin (et le tien).
2. phpMyAdmin → onglet **SQL** :
   ```sql
   UPDATE utilisateurs SET est_admin = 1 WHERE email = 'email.du.cousin@gmail.com';
   ```
3. Déconnexion / reconnexion → le lien **Admin** apparaît en haut.

## 7. Remplir la boutique

- **Admin → Produktet → + Produkt i ri** : nom, catégorie, prix, photo, puis tailles/couleurs/stock.
- Dans `config/app.php` (sur ton PC, puis renvoie le fichier) :
  - `PAYS_LIVRAISON` : les frais de livraison par pays ;
  - `CONTACT_TELEPHONE`, `LIEN_INSTAGRAM`, `LIEN_TIKTOK` : affichés en bas de chaque page.

## 8. Test final (à ne pas sauter)

- [ ] Le cadenas 🔒 s'affiche, et `http://` redirige vers `https://`
- [ ] `https://tondomaine.com/database/hebergement.sql` → **404** (fichiers internes protégés)
- [ ] Passer une **vraie commande test** depuis un téléphone
- [ ] L'**email d'alerte** arrive chez le cousin (regarde aussi dans **Spam** → « Pas un spam »)
- [ ] Le stock a baissé dans l'admin
- [ ] **Annuler** la commande test dans l'admin → le stock revient

---

## Ensuite, au quotidien

- **Sauvegardes** : la plupart des hébergeurs en font chaque jour. Sinon, une fois par semaine :
  phpMyAdmin → Exporter, et télécharge `public/img/produits/` par FTP.
- **Modifier le code** : teste d'abord sur Laragon, puis renvoie seulement les fichiers changés.
  Ne remplace jamais `config/production.php` ni `public/img/produits/` sur le serveur.
- **Une erreur sur le site ?** Regarde `logs/erreurs.log` sur le serveur (par FTP).

## En local (Laragon)

- Site : `http://dripfit.ks.test`
- Emails envoyés par le site (alertes de commande) : **`http://localhost:8025`** (Mailpit — rien ne part vraiment)
