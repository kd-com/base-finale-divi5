# Présentation développeur : créer un block kd-com

## Structure recommandée
- Placez vos blocks dans le dossier `blocks/` du thème.
- Un block est composé d’un fichier de déclaration (dans `blocks_declaration/`), d’un template de rendu (dans `my_block/`), et éventuellement de champs ACF (dans `acf_fields/`).

## Étapes pour créer un block
1. **Créer le fichier de déclaration** : Ajoutez un fichier PHP dans `blocks_declaration/` et utilisez `acf_register_block_type()`.
2. **Créer le template** : Ajoutez le fichier de rendu dans `my_block/`.
3. **Définir les champs ACF** : Ajoutez le groupe de champs dans l’admin ACF et exportez-le dans `acf_fields/` si besoin.
4. **Personnaliser le style** : Ajoutez les styles dans le dossier SASS ou CSS du thème.
5. **Documenter** : Ajoutez une page dans ce dossier wiki pour expliquer le fonctionnement à l’utilisateur.

## Bonnes pratiques
- Utilisez des noms explicites pour les blocks et les champs.
- Documentez chaque paramètre et option.
- Prévoyez des messages d’erreur clairs pour l’utilisateur.
- Testez le block en admin et en front.
