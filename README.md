# MdgHomePush
* @author [Michel Dumont](https://michel.dumont.io)
* @version **1.0.0**
* @package **Prestashop 1.6 - 1.7**

# Description
- Afficher les slides et pushes sur la page d'accueil

# Intégration
- Le module utilise des sousdossiers **v16** et **v17** pour organiser les templates en fonction de la version de Prestashop
- La mise en forme est réalisée en utilisant **compass**
- Les librairies telles que **Splide** pour la gestion des diaporamas se trouvent dans le dossier views/libs/nomDeLeDependance


# Développement
## Les Models
### ObjectModel
Ce model gères les fonctions communes à tous les modèles du module.
Dans les dossiers **v16** et **v17** sont présents des surcouches spécifiques à la version de Prestashop qui est utilisée.

### SlideModel + SlideForm
Permet de gérer les slides

### PushModel + PushForm
Permet de gérer les pushes

### ConfigurationForm
Permet de gérer la configuration du module
