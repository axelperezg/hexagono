<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Seed the default contacts. Safe to re-run.
     */
    public function run(): void
    {
        $contacts = [
            ['name' => 'Lic. Margarita Natividad Trujillo Olea', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL ACAPULCO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Karen Judith Guillén Medina', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL ALTAMIRA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Adrián Lara Márquez', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL COATZACOALCOS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Yuridia Lozano Pioquinto', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL DOS BOCAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Amador Arteaga Sahagún', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL ENSENADA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Guillermo Von Borstel Osuna', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL GUAYMAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. María Agustina Álvarez Martínez', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL LÁZARO CÁRDENAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. Julieta Juárez Ochoa', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL MANZANILLO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lae. Miguel Eduardo Ramírez Lizárraga', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL MAZATLÁN', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Elisa Gianina Capellini Álvarez', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL PROGRESO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Miguel Ángel Maya Álvarez', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL PUERTO VALLARTA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Rafael Gerardo Sánchez Mercado', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL SALINA CRUZ', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Dr. Asunción González Montiel', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL TAMPICO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. María del Pilar Calleja Ramírez', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL TOPOLOBAMPO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Evodio Cruz Segura', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL TUXPAN', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Adrián García Saldaña', 'organization' => 'ADMINISTRACIÓN DEL SISTEMA PORTUARIO NACIONAL VERACRUZ', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Mauricio Sosa Ocaña', 'organization' => 'AEROPUERTO INTERNACIONAL DE LA CIUDAD DE MÉXICO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Dilery Urenda García', 'organization' => 'AEROPUERTO INTERNACIONAL FELIPE ÁNGELES', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Lizbeth Quezada Castro', 'organization' => 'AEROPUERTOS Y SERVICIOS AUXILIARES', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Eduardo Marín Conde', 'organization' => 'BANCO NACIONAL DE COMERCIO EXTERIOR', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtro. Jesús Ulises Pérez Barba', 'organization' => 'BANCO NACIONAL DE OBRAS Y SERVICIOS PÚBLICOS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Cor. Inf. E.M. Carlos Cisneros Carpintero', 'organization' => 'BANCO NACIONAL DEL EJÉRCITO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Cynthia Ramírez Villarreal', 'organization' => 'CAMINOS Y PUENTES FEDERALES DE INGRESOS Y SERVICIOS CONEXOS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Mauricio García Fernández', 'organization' => 'CASA DE MONEDA DE MÉXICO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'María de Lourdes Villanueva', 'organization' => 'CENTRO DE INNOVACIÓN APLICADA EN TECNOLOGÍAS COMPETITIVAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtro. Iván Joseph Luna Landa', 'organization' => 'CENTRO NACIONAL DE CONTROL DEL GAS NATURAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Martha Yuriria Rodríguez Estrada', 'organization' => 'COMISIÓN EJECUTIVA DE ATENCIÓN A VÍCTIMAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Nidia Aracely Espinoza Martinez', 'organization' => 'COMISIÓN NACIONAL DE ACUACULTURA Y PESCA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Dr. José Feliciano González Jiménez', 'organization' => 'COMISIÓN NACIONAL DE ÁREAS NATURALES PROTEGIDAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Gretel Gertrudis González Moreno', 'organization' => 'COMISIÓN NACIONAL DE CULTURA FÍSICA Y DEPORTE', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Fernando Shimizu Durán', 'organization' => 'COMISIÓN NACIONAL DE LIBROS DE TEXTO GRATUITOS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtro. Javier Buenrostro Sánchez', 'organization' => 'COMISIÓN NACIONAL DEL AGUA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Vanessa Liliana Prieto Barrientos', 'organization' => 'COMISIÓN NACIONAL DEL SISTEMA DE AHORRO PARA EL RETIRO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'David Cabrera Hermosillo', 'organization' => 'COMISIÓN NACIONAL FORESTAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Dr. Néstor Daniel Luna González', 'organization' => 'COMISIÓN NACIONAL PARA EL USO EFICIENTE DE LA ENERGÍA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. Elodia Ivonne Hernández Contreras', 'organization' => 'COMISIÓN NACIONAL PARA LA PROTECCIÓN Y DEFENSA DE LOS USUARIOS DE SERVICIOS FINANCIEROS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Roberto Nogami Monteverde', 'organization' => 'COMPAÑÍA OPERADORA DEL CENTRO CULTURAL Y TURÍSTICO DE TIJUANA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Claudia Muñoz Espíndola', 'organization' => 'CONSEJO NACIONAL PARA PREVENIR LA DISCRIMINACIÓN', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Rodolfo Antonio Osorio de Carrerá', 'organization' => 'FIDEICOMISO DE FOMENTO MINERO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Dra. Yesenia Guadalupe Castañeda Fernández', 'organization' => 'FINANCIERA PARA EL BIENESTAR', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Sandra Elizabeth Montoya Cárdenas', 'organization' => 'FONDO DE CULTURA ECONÓMICA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. Lizbeth Liliana Mendoza Chávez', 'organization' => 'FONDO NACIONAL DE FOMENTO AL TURISMO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'José Jarero Valencia', 'organization' => 'FONDO NACIONAL PARA EL FOMENTO DE LAS ARTESANÍAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Antonio Canchola Castro', 'organization' => 'GRUPO AEROPORTUARIO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Gral. Brig. I.I. Ret., Ivo Samano Landa', 'organization' => 'INSTITUTO DE SEGURIDAD SOCIAL PARA LAS FUERZAS ARMADAS MEXICANAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Jorge Aguilera Cercado', 'organization' => 'INSTITUTO DE SEGURIDAD Y SERVICIOS SOCIALES DE LOS TRABAJADORES DEL ESTADO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Emilio Vázquez Pérez', 'organization' => 'INSTITUTO DEL FONDO NACIONAL PARA EL CONSUMO DE LOS TRABAJADORES', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mariana Cruz Linares', 'organization' => 'INSTITUTO MEXICANO DE CINEMATOGRAFÍA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Alejandra Cerecedo Constantino', 'organization' => 'INSTITUTO MEXICANO DE LA JUVENTUD', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Dorilita Mora Jurado', 'organization' => 'INSTITUTO MEXICANO DE LA PROPIEDAD INDUSTRIAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Laura Franco Hernández', 'organization' => 'INSTITUTO MEXICANO DE LA RADIO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Dra. Elizabeth Mar Juárez', 'organization' => 'INSTITUTO MEXICANO DEL PETRÓLEO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Amadeo Díaz Moguel', 'organization' => 'INSTITUTO MEXICANO DEL SEGURO SOCIAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Gabriel Ulises Leyva Rendón', 'organization' => 'INSTITUTO NACIONAL DE ANTROPOLOGÍA E HISTORIA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Guadalupe Rivera Loy', 'organization' => 'INSTITUTO NACIONAL DE ASTROFÍSICA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Aarón Polo López', 'organization' => 'INSTITUTO NACIONAL DE BELLAS ARTES Y LITERATURA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Jorge Alberto Valencia Sandoval', 'organization' => 'INSTITUTO NACIONAL DE LAS PERSONAS ADULTAS MAYORES', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Rogelio Hermenegildo García', 'organization' => 'INSTITUTO NACIONAL DE LENGUAS INDÍGENAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. Floriberta Faustino Reyes', 'organization' => 'INSTITUTO NACIONAL DE LOS PUEBLOS INDÍGENAS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. Karina Lujan Lujan', 'organization' => 'INSTITUTO NACIONAL DEL DERECHO DE AUTOR', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. Claudia Díaz Arnaud', 'organization' => 'INSTITUTO NACIONAL PARA LA EDUCACIÓN DE LOS ADULTOS', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Vicente Vargas González', 'organization' => 'INSTITUTO PARA LA PROTECCIÓN AL AHORRO BANCARIO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Marco Antonio Ramírez Urbina', 'organization' => 'INSTITUTO POLITÉCNICO NACIONAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'C. Luis Rubén Sánchez Martínez', 'organization' => 'LOTERÍA NACIONAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Eduardo Marín Conde', 'organization' => 'NACIONAL FINANCIERA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Blanca Cecilia Navarro González', 'organization' => 'PROCURADURÍA FEDERAL DE PROTECCIÓN AL AMBIENTE', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. Vanessa Monserrat Romero Zavala', 'organization' => 'PROCURADURÍA FEDERAL DEL CONSUMIDOR', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Dra. Adriana Solórzano Fuentes', 'organization' => 'RADIO EDUCACIÓN', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Lucy Elena Sánchez Díaz', 'organization' => 'SECRETARÍA ANTICORRUPCIÓN Y BUEN GOBIERNO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Alexis Emmanuel Juárez Martínez', 'organization' => 'SECRETARÍA DE AGRICULTURA Y DESARROLLO RURAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Lidia Arce Navarijo', 'organization' => 'SECRETARÍA DE BIENESTAR', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lcda. Nurit Martínez Carballo', 'organization' => 'SECRETARÍA DE CIENCIA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Ulises Ortega González', 'organization' => 'SECRETARÍA DE CULTURA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Héctor Montaut Casas', 'organization' => 'SECRETARÍA DE ECONOMÍA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Miguel Ángel Pineda Baltazar', 'organization' => 'SECRETARÍA DE EDUCACIÓN PÚBLICA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Octavio Ortega Velio Mejía', 'organization' => 'SECRETARÍA DE ENERGÍA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Manuel Durán Aguirre', 'organization' => 'SECRETARÍA DE GOBERNACIÓN', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Wilhem Friedrich Hagelsieb Garza', 'organization' => 'SECRETARÍA DE HACIENDA Y CRÉDITO PÚBLICO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Wendy Vanessa Roa Coronado', 'organization' => 'SECRETARÍA DE INFRAESTRUCTURA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'General Brigadier E.M. Enrique Mejía Nicolás', 'organization' => 'SECRETARÍA DE LA DEFENSA NACIONAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. María Sandra Licona Morales', 'organization' => 'SECRETARÍA DE LAS MUJERES', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Contralmirante Rafael Antonio Lagunes Arteaga', 'organization' => 'SECRETARÍA DE MARINA', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'José Manuel Gutiérrez Minera', 'organization' => 'SECRETARÍA DE MEDIO AMBIENTE Y RECURSOS NATURALES', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Omar Ameth Wong Camarillo', 'organization' => 'SECRETARÍA DE RELACIONES EXTERIORES', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Carlos Álvaro Mateos Beltrán', 'organization' => 'SECRETARÍA DE SALUD', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Alberto Casas García', 'organization' => 'SECRETARÍA DE TURISMO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Lic. Diego Camacho Aquiahuatl', 'organization' => 'SECRETARÍA DEL TRABAJO Y PREVISIÓN SOCIAL', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Paula Cristina Neves Nogueira Leite', 'organization' => 'SECRETARÍA GENERAL DEL CONSEJO NACIONAL DE POBLACIÓN', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Ing. Carmen Esquivel Miranda', 'organization' => 'SERVICIO GEOLÓGICO MEXICANO', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Blanca Palafox López', 'organization' => 'SERVICIO NACIONAL DE SANIDAD', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Mtra. Vianey Berenice Fernández Muciño', 'organization' => 'SERVICIOS DE SALUD DEL INSTITUTO MEXICANO DEL SEGURO SOCIAL PARA EL BIENESTAR', 'position' => 'Titular de Comunicación Social'],
            ['name' => 'Victor Hugo Marín Cervantes', 'organization' => 'TREN MAYA', 'position' => 'Titular de Comunicación Social'],
        ];

        foreach ($contacts as $contact) {
            $organization = Organization::where('name', $contact['organization'])->first();

            if (! $organization) {
                continue;
            }

            Contact::updateOrCreate(
                ['name' => $contact['name'], 'organization_id' => $organization->id],
                ['position' => $contact['position'], 'is_primary' => true],
            );
        }
    }
}
