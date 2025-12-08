# 📄 Cahier des Charges Fonctionnel – Application VENUS

**VENUS** est une application web conçue pour l'automatisation et l'industrialisation du traitement de photos d'identité scolaire, en y superposant les informations textuelles des élèves.

## 1. Contexte et Objectifs

L'objectif principal est de fournir un **gain de temps significatif** dans la préparation des documents scolaires (demi-cartes photo) via trois modes de fonctionnement distincts :

* **Traitement Unitaire :** Gestion rapide des cas individuels.
* **Traitement par Lot (Batch) :** Production de masse pour des classes entières.
* **Module de Correction :** Réédition ciblée des erreurs.

---

## 2. Spécifications Fonctionnelles

### 🚀 Module 1 : Traitement Unitaire (Single Processing)

Ce module permet de traiter la photo d'un seul élève en temps réel via une interface graphique interactive.

#### 2.1. Fonctionnalités de l'Interface

| Section | Élément | Description |
| :--- | :--- | :--- |
| **Section Gauche** (Formulaire de Saisie) | **Données Élève** | Nom complet, Classe, Code élève. |
| | **Paramètres de Design** | Couleur du texte, Date, Taille de la police. |
| | **Upload** | Chargement de la photo unique (Formats : JPG/PNG). |
| **Section Droite** (Résultat) | **Prévisualisation** | Affichage immédiat de la photo traitée. |
| | **Action** | Bouton "Télécharger" pour récupérer l'image finale. |

#### 2.2. Actions et Contraintes

* **Bouton d'Action :** "Save" ou "Traiter" pour lancer la fusion des données sur l'image.
* **Verrouillage IHM :** Durant l'exécution du traitement, tous les champs de saisie et le bouton d'upload doivent être **désactivés (verrouillés)** pour éviter les conflits d'entrée.
* **Notifications :** Alertes visuelles obligatoires en cas d'échec (format de fichier incorrect, champs manquants) ou notification de succès.

### 🏭 Module 2 : Traitement par Lot (Batch Processing)

Ce module permet l'industrialisation et la génération d'un grand nombre de cartes avec une organisation hiérarchique.

#### 2.3. Workflow (Flux de travail)

1.  **Configuration de la Hiérarchie :**
    * Création ou sélection de l'Année Scolaire.
    * Création de l'Établissement (Champs : Nom, Code).
    * Création de la Classe rattachée à l'établissement.
2.  **Importation des Données :**
    * Chargement d'un fichier **Excel/CSV** contenant la liste des élèves de la classe sélectionnée.
    * Chargement des **Photos des élèves** (fichiers individuels).
    * > **⚠️ Contrainte Critique :** Les photos doivent être chargées dans l'**ordre exact** correspondant aux lignes du fichier Excel pour garantir la correspondance.
3.  **Exécution et Sortie :**
    * Lancement du traitement global.
    * Génération et téléchargement automatique d'un **fichier compressé (.ZIP)** contenant toutes les photos traitées et organisées.

### 🩹 Module 3 : Module de Correction

Ce module est dédié à la rectification ciblée d'erreurs d'une session précédente.

#### 2.4. Workflow

1.  **Identification de la Session :**
    * Sélection d'une Date.
    * Saisie d'une **Étiquette (Label)** identifiant le lot concerné.
2.  **Importation des Correctifs :**
    * Chargement d'un fichier **Excel "Correction"** contenant *uniquement* les élèves à corriger (Nom, Code, Classe).
    * Chargement des **nouvelles photos** (ou photos à retraiter) dans l'ordre correspondant.
3.  **Validation de Cohérence :**
    * Le système doit vérifier la **correspondance numérique** (ex : si le fichier Excel contient 10 lignes, 10 fichiers photos doivent être chargés).
4.  **Traitement :**
    * Génération des nouvelles cartes corrigées.

---

## 3. Contraintes Techniques et Ergonomiques

| Domaine | Spécification |
| :--- | :--- |
| **Formats de Fichiers** | **Images :** `.jpg`, `.jpeg`, `.png` (redimensionnées au préalable). **Données :** `.xlsx`, `.xls`, `.csv`. |
| **Performance** | Le traitement en lot doit être géré de manière **asynchrone** (utilisation des Queues Laravel recommandée) pour ne pas bloquer le navigateur (timeout). |
| **Interface Utilisateur (UI)** | Interface épurée, scindée clairement entre la zone de configuration (**gauche**) et la zone de résultat (**droite**) pour le traitement unitaire. |
| **Nommage des Fichiers** | Les fichiers finaux générés doivent suivre une nomenclature standard et lisible (Exemple : `Code_Nom.jpg`) pour faciliter leur gestion ultérieure. |