[Retour](index.md)

## Vue apprenant ##

Le bloc s'affiche en fonction du nombre de formation suivit par l'apprenant.

### L'apprenant ne suit aucune formation concernant le cours ###  
Le bloc ne s'affiche pas et aucune mise en évidence n'est réalisée.  

### L'apprenant suit une formation concernant le cours ###

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
* Le bouton Attestation  
    dépend à la fois du paramétrage et de l'existence d'attestation pour cet apprenant.

### La liste des Attestations


