# 🔍 IP Tracker con Redirección Invisible - Análisis de Seguridad

## 📖 Introducción

En este repositorio se documenta un **ejercicio de análisis de seguridad** que implementa un sistema de tracking de IPs mediante una técnica de **ingeniería social controlada**. El sistema fue desarrollado para demostrar cómo un atacante podría capturar información sensible de usuarios mediante un enlace aparentemente inocente.

El objetivo de este proyecto es **concienciar sobre los riesgos de seguridad** asociados con hacer clic en enlaces no verificados y demostrar la importancia de implementar medidas de protección adecuadas tanto a nivel personal como empresarial.

## 🎯 Objetivos del Análisis

1. **Demostrar** cómo se puede obtener información de usuarios mediante técnicas de phishing
2. **Analizar** la efectividad de herramientas de tunneling como Ngrok para evadir medidas de seguridad
3. **Concienciar** sobre la importancia de verificar enlaces antes de hacer clic
4. **Proporcionar** recomendaciones de seguridad para prevenir este tipo de ataques

## 📑 Índice

1. [🔧 Configuración del Entorno](#configuración-del-entorno)
2. [🌐 Exposición con Ngrok](#exposición-con-ngrok)
3. [📊 Análisis de VirusTotal](#análisis-de-virustotal)
4. [📝 Captura de Datos](#captura-de-datos)
5. [⚠️ Riesgos Identificados](#riesgos-identificados)
6. [🛡️ Recomendaciones de Seguridad](#recomendaciones-de-seguridad)
7. [🎯 Conclusión](#conclusión)

## 🔧 Configuración del Entorno

### Script de Preparación
Se utilizó el script [`preparacion.sh`](preparacion.sh) para configurar un entorno controlado:

```bash
# Ejecución del script de preparación
chmod +x preparacion.sh
sudo ./preparacion.sh
```

El script realizó las siguientes acciones:
- ✅ Limpieza del directorio `/var/www/html`
- ✅ Instalación de Apache y PHP
- ✅ Configuración de permisos adecuados
- ✅ Creación del archivo de logs `ips.txt`

![Configuración de Permisos](creación-y-permisos-de-index-y-txt.png)
*Configuración de permisos para los archivos del sistema*

### Autenticación con Ngrok
```bash
# Configuración del token de autenticación
ngrok config add-authtoken [TOKEN]
```

![Autenticación Ngrok](ngrok-token.png)
*Autenticación exitosa en el servicio Ngrok*

## 🌐 Exposición con Ngrok

### Tunnel Configurado
Se estableció un tunnel seguro mediante Ngrok para exponer el servidor local:

```bash
# Inicio del tunnel en el puerto 80
ngrok http 80
```

![Panel de Control Ngrok](ngrock-exponiendolo-al-mundo.png)
*Panel de control de Ngrok mostrando el tunnel activo y las estadísticas de conexión*

### Enlace Generado
Ngrok proporcionó un enlace único:
```
https://[SUBDOMINIO].ngrok-free.app
```

### Advertencia de Seguridad
Los usuarios que accedieron al enlace vieron una página de advertencia:

![Advertencia Ngrok](aviso-de-ngrok-gratuito.png)
*Página de advertencia que muestra Ngrok para enlaces gratuitos*

## 📊 Análisis de VirusTotal

El enlace generado fue analizado mediante VirusTotal para evaluar su detección:

![Análisis VirusTotal](analisis-con-virustotal.png)
*Resultado del análisis en VirusTotal - Solo 2/98 motores detectaron como potencialmente malicioso*

**Resultados del análisis:**
- 🔍 **2/98** motores de antivirus detectaron el enlace como malicioso
- ✅ **96/98** no mostraron detecciones
- ⚠️ Las detecciones fueron clasificadas como **falsos positivos**

## 📝 Captura de Datos

### Acceso de la Víctima
Cuando un usuario accedió al enlace, el sistema capturó automáticamente:

![Acceso de Víctima](victima-accediendo-al-enlace.png)
*Usuario accediendo al enlace de Ngrok*

### Información Capturada
El sistema registró información detallada del visitante:

![Datos Capturados](victima-capturada.png)
*Información detallada capturada del usuario que accedió al enlace*

**Datos obtenidos:**
- 🌍 **Ubicación geográfica** (país, región, ciudad)
- 📡 **Dirección IP** y proveedor de internet (ISP)
- 🖥️ **Navegador y sistema operativo** utilizado
- 🌐 **Idioma preferido** y página de referencia
- ⏰ **Fecha y hora exacta** del acceso

## ⚠️ Riesgos Identificados

### 1. Ingeniería Social Efectiva
- Los enlaces de Ngrok parecen **legítimos** a simple vista
- La página de advertencia es **fácilmente omitible** por usuarios no técnicos
- La redirección inmediata a Google **reduce las sospechas**

### 2. Evasión de Detección
- Solo **2%** de los motores antivirus detectaron la amenaza
- Las herramientas de tunneling **eluden muchas medidas** de seguridad perimetral
- El uso de HTTPS **enmascara** el tráfico malicioso

### 3. Captura de Información Sensible
- Obtención de **datos de geolocalización** precisos
- Identificación del **proveedor de internet**
- Captura de **huella digital** del navegador

## 🛡️ Recomendaciones de Seguridad

### Para Usuarios Finales
1. **🔍 Verificar Enlaces**
   - Examinar URLs antes de hacer clic
   - Utilizar herramientas de análisis de enlaces

2. **🛡️ Navegación Segura**
   - Utilizar extensiones de seguridad en el navegador
   - Mantener el navegador actualizado

3. **🌐 Concienciación**
   - Educarse sobre técnicas de phishing
   - Desconfiar de enlaces acortados o desconocidos

### Para Empresas
1. **🔒 Seguridad Perimetral**
   - Implementar filtrado web avanzado
   - Bloquear servicios de tunneling conocidos

2. **📊 Monitoreo**
   - Implementar soluciones de detección de phishing
   - Monitorear tráfico saliente inusual

3. **🎓 Capacitación**
   - Entrenar empleados en识别 phishing
   - Realizar simulacros de ataques controlados

### Para Desarrolladores
1. **⚙️ Configuración Segura**
   - Deshabilitar información sensible en headers
   - Implementar políticas de seguridad de contenido

2. **🔍 Auditoría Regular**
   - Realizar tests de penetración periódicos
   - Monitorear logs de acceso en busca de anomalías

## 🎯 Conclusión

Este ejercicio demostró la **efectividad de las técnicas de ingeniería social** combinadas con herramientas de tunneling modernas. La facilidad con que se puede capturar información sensible destaca la **importancia crítica de la educación en seguridad** y la implementación de **múltiples capas de defensa**.

La **baja tasa de detección** en herramientas antivirus tradicionales subraya la necesidad de adoptar **enfoques de seguridad más proactivos** y basados en comportamiento.

---

**⚖️ Nota Legal**: Este análisis se realizó en un **entorno controlado** con fines educativos. El **testing de seguridad** debe realizarse únicamente en sistemas con **autorización explícita** del propietario.

**🔔 Disclaimer**: Este documento es solo con fines educativos. No me hago responsable del mal uso de esta información.

**📧 Contacto**: Para reportar vulnerabilidades o solicitar más información sobre este análisis.
