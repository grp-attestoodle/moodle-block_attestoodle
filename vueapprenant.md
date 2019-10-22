[Retour](index.md)

## Vue apprenant ##

Le bloc s'affiche en fonction du nombre de formation suivit par l'apprenant.

### 1) L'apprenant ne suit aucune formation concernant le cours ###  
Le bloc ne s'affiche pas et aucune mise en évidence n'est réalisée.  

### 2) L'apprenant suit une formation concernant le cours ###

Les jalons du cours sont mis en évidence avec :  
![neo2](https://user-images.githubusercontent.com/26385729/67185724-bec32b00-f3e6-11e9-90c2-0ea5c722c120.gif)

Détail de l'affichage du bloc:  
![apprenant1](https://user-images.githubusercontent.com/26385729/67185566-6ee46400-f3e6-11e9-9e49-85c4f5792cd4.png)

Certains éléments peuvent ne pas être présent :
 * Le nombre de jalons franchit  
    cet affichage est soumis au paramétrage du bloc
 * Echéance de la prochaine attestation   
    cet affichage est soumis au paramétrage du bloc et nécessite la présence du plugin tool_taskattestoodle
 * L'estimation d'avancement  
    soumis au paramétrage et nécessite les dates de début et de fin de formation ainsi que la durée théorique de la formation. 
* Le bouton Attestations  
    dépend à la fois du paramétrage et de l'existence d'attestation pour cet apprenant.

#### Remarque ####
La liste des cours permet de naviguer entre les cours de la même formation.

### 3) L'apprenant suit n formations concernant le cours ###
Le bloc est enrichit par la liste des formations, par défaut la dernière formation de la liste est sélectionnée.  

![learner_n](https://user-images.githubusercontent.com/26385729/67265275-9d794200-f4ad-11e9-83bd-63e2e8c636d6.png)


Il est possible de cliquer sur une formation pour changer la formation affichée.

![learner_n2](https://user-images.githubusercontent.com/26385729/67265430-02cd3300-f4ae-11e9-9d15-652a24a5de1b.png)
*Ici l'estimation d'avancement n'est pas affichée, car la durée théorique de la formation Dev attestoodle clone 4_2020 n'est pas renseignée*


### La liste des attestations ###
La liste des attestations est triée par date de génération, l'apprenant peut télécharger chaque attestation de cette liste.
![lstattest](https://user-images.githubusercontent.com/26385729/67265987-673cc200-f4af-11e9-8129-1eabec2c801f.png)

Cette liste est suivi du graphique présentant l'avancement au niveau de chaque génération d'attestation.
![graph](https://user-images.githubusercontent.com/26385729/67266222-f6e27080-f4af-11e9-8d0b-7a68627454d5.png)




