<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\Apoderado;
use App\Models\Estudiante;
use App\Models\Grado;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PrimariaSeeder extends Seeder
{
    private const GRUPOS = [
        [
            'grado_orden' => 1,
            'seccion' => 'A',
            'estudiantes' => [
                [
                    'dni' => '91781213',
                    'nombres' => 'ITZAYANA DEL PILAR',
                    'apellidos' => 'AGUERO LOAYZA',
                    'padre' => ['dni' => '45343292', 'nombres' => 'WALTER JEAMPIERR', 'apellidos' => 'AGUERO COLINA', 'sexo' => 'M'],
                    'madre' => ['dni' => '41704627', 'nombres' => 'VANESSA DEL PILAR', 'apellidos' => 'LOAYZA GRANADOS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91263550',
                    'nombres' => 'GUSTAVO IAN',
                    'apellidos' => 'ALEGRE ARRIETA',
                    'padre' => ['dni' => '42594189', 'nombres' => 'GABRIEL ANTONIO', 'apellidos' => 'ALEGRE VELAYARSE', 'sexo' => 'M'],
                    'madre' => ['dni' => '72492927', 'nombres' => 'AMELIA ROCIO', 'apellidos' => 'ARRIETA SANDOVAL', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91635387',
                    'nombres' => 'DYLAN ZAID',
                    'apellidos' => 'ARONES MELGAR',
                    'padre' => ['dni' => '44283821', 'nombres' => 'FREDERICK ANTONY', 'apellidos' => 'ARONES SALINAS', 'sexo' => 'M'],
                    'madre' => ['dni' => '44829805', 'nombres' => 'DENISSE VANESA', 'apellidos' => 'MELGAR CAMPOS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91434108',
                    'nombres' => 'BRIANNA LUCIANA',
                    'apellidos' => 'ARRIETA TOVAR',
                    'padre' => ['dni' => '71224595', 'nombres' => 'EDER MAXIMO', 'apellidos' => 'ARRIETA CHAGUA', 'sexo' => 'M'],
                    'madre' => ['dni' => '73075066', 'nombres' => 'NADIA LISETH', 'apellidos' => 'TOVAR VILLEGAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91494599',
                    'nombres' => 'THIAGO DYLAN',
                    'apellidos' => 'AZA' . "\u{00D1}" . 'A CHANCHARI',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '45650129', 'nombres' => 'WILIAM', 'apellidos' => 'AZA' . "\u{00D1}" . 'A GAVIDIA', 'sexo' => 'M'],
                    'madre' => ['dni' => '61902436', 'nombres' => 'LADDY', 'apellidos' => 'CHANCHARI ROJAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91672271',
                    'nombres' => 'NICOLAS JHONATAN',
                    'apellidos' => 'CARHUALLANQUI CANEPA',
                    'padre' => ['dni' => '71328246', 'nombres' => 'JUNIOR JHONATAN', 'apellidos' => 'CARHUALLANQUI AGUIRRE', 'sexo' => 'M'],
                    'madre' => ['dni' => '71090622', 'nombres' => 'RODINA', 'apellidos' => 'CANEPA MICHI', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91598327',
                    'nombres' => 'AMIRA EVANGELINA',
                    'apellidos' => 'HUAMANCHA SALVADOR',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '41467069', 'nombres' => 'SEBASTIAN DARWIN', 'apellidos' => 'HUAMANCHA AGUILAR', 'sexo' => 'M'],
                    'madre' => ['dni' => '44504447', 'nombres' => 'MARIA ESTHER', 'apellidos' => 'SALVADOR POMALAZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91577710',
                    'nombres' => 'SHERLY YARETZY',
                    'apellidos' => 'LINO ATENCIA',
                    'padre' => ['dni' => '47574428', 'nombres' => 'DAVID', 'apellidos' => 'LINO CALERO', 'sexo' => 'M'],
                    'madre' => ['dni' => '74578652', 'nombres' => 'YERIKA', 'apellidos' => 'ATENCIA RIVERA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91426414',
                    'nombres' => 'MIA VALENTINA',
                    'apellidos' => 'MEDRANO PE' . "\u{00D1}" . 'A',
                    'observacion' => 'H-',
                    'padre' => ['dni' => '42699559', 'nombres' => 'DANIEL ENRIQUE', 'apellidos' => 'MEDRANO ORIZANO', 'sexo' => 'M'],
                    'madre' => ['dni' => '43336170', 'nombres' => 'ROSA ELIZABETH', 'apellidos' => 'PE' . "\u{00D1}" . 'A MENDIZABAL', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91329814',
                    'nombres' => 'NAYADE SAMARA',
                    'apellidos' => 'PACHO PEREZ',
                    'padre' => ['dni' => '47351175', 'nombres' => 'JORGE LUIS', 'apellidos' => 'PACHO MATIAS', 'sexo' => 'M'],
                    'madre' => ['dni' => '71883251', 'nombres' => 'LOURDES', 'apellidos' => 'PEREZ TRINIDAD', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91258811',
                    'nombres' => 'AYRTON SANTIAGO',
                    'apellidos' => 'PANDURO MARTINEZ',
                    'padre' => null,
                    'madre' => ['dni' => '45980009', 'nombres' => 'SAMMY HEIDDY', 'apellidos' => 'MARTINEZ ESPINOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91584435',
                    'nombres' => 'VALERIA LUZ',
                    'apellidos' => 'ROMAN MENDOZA',
                    'observacion' => 'H-',
                    'padre' => ['dni' => '41284989', 'nombres' => 'ANIBAL ENRIQUE', 'apellidos' => 'ROMAN VELA', 'sexo' => 'M'],
                    'madre' => ['dni' => '40259790', 'nombres' => 'JACQUELENI', 'apellidos' => 'MENDOZA ARIAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91519509',
                    'nombres' => 'VALENTINA EMILIA',
                    'apellidos' => 'TARAZONA ESPIRITU',
                    'padre' => ['dni' => '46992335', 'nombres' => 'ANDY', 'apellidos' => 'TARAZONA JANAMPA', 'sexo' => 'M'],
                    'madre' => ['dni' => '71088630', 'nombres' => 'TANIA', 'apellidos' => 'ESPIRITU ESPINOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91655906',
                    'nombres' => 'ETHAN JONATHAN',
                    'apellidos' => 'YUPARI TUDELA',
                    'observacion' => 'H-',
                    'padre' => ['dni' => '10808612', 'nombres' => 'VICTOR JONATHAN', 'apellidos' => 'YUPARI CAPDEVILA', 'sexo' => 'M'],
                    'madre' => ['dni' => '47837142', 'nombres' => 'ANETTE MILAGROS', 'apellidos' => 'TUDELA THEO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91381329',
                    'nombres' => 'ANDRE CALEB',
                    'apellidos' => 'GUISADO NOLE',
                    'padre' => ['dni' => '80126292', 'nombres' => 'ROBERTO CARLOS', 'apellidos' => 'GUISADO SINCHI', 'sexo' => 'M'],
                    'madre' => ['dni' => '75854252', 'nombres' => 'ANGIE THATIANA', 'apellidos' => 'NOLE CASTILLO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91582399',
                    'nombres' => 'KATALEYA AMY',
                    'apellidos' => 'CASTRO TORRES',
                    'padre' => ['dni' => '44587693', 'nombres' => 'EUSEBIO ALEX', 'apellidos' => 'CASTRO SORIA', 'sexo' => 'M'],
                    'madre' => ['dni' => '48418283', 'nombres' => 'MILAGROS LAURA', 'apellidos' => 'TORRES SOSA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91254207',
                    'nombres' => 'JEICOB MAKENS (INTERNO)',
                    'apellidos' => 'VARGAS PAUCAR',
                    'padre' => ['dni' => null, 'nombres' => 'ANTONY MAKENS', 'apellidos' => 'VARGAS RODRIGUEZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '73126654', 'nombres' => 'ZOAV GEORGUIA', 'apellidos' => 'PAUCAR ALANIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91437648',
                    'nombres' => 'HELAM ALI MATTHEWUS',
                    'apellidos' => 'ANTAZU BOTTGER',
                    'padre' => ['dni' => '44477348', 'nombres' => 'PAUL ERNESTO', 'apellidos' => 'ANTAZU ARECHE', 'sexo' => 'M'],
                    'madre' => ['dni' => '70006911', 'nombres' => 'DEYSI MAGALI', 'apellidos' => 'BOTTGER MICHI', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91800316',
                    'nombres' => 'YHORYWI',
                    'apellidos' => 'GUEVARA CHAHUA',
                    'padre' => ['dni' => '04346854', 'nombres' => 'LUIS', 'apellidos' => 'GUEVARA AMEZAGA', 'sexo' => 'M'],
                    'madre' => ['dni' => '44241884', 'nombres' => 'ELVIA MARUJA', 'apellidos' => 'CHAHUA MALPARTIDA', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 1,
            'seccion' => 'B',
            'estudiantes' => [
                [
                    'dni' => '78524483',
                    'nombres' => 'EDUARDO JHON (INTERNO)',
                    'apellidos' => 'AQUINO CUEVA',
                    'padre' => null,
                    'madre' => ['dni' => '46963409', 'nombres' => 'YADILIS', 'apellidos' => 'CUEVA MALPARTIDA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91415063',
                    'nombres' => 'SCARLET KHALEESY',
                    'apellidos' => 'ALVARADO YALICO',
                    'padre' => ['dni' => '44294713', 'nombres' => 'YODITA', 'apellidos' => 'ABUELA-ALMERCO BERROSPI', 'sexo' => 'F'],
                    'madre' => ['dni' => '62714928', 'nombres' => 'KARINA', 'apellidos' => 'YALICO ALMERCO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91581493',
                    'nombres' => 'JHONATAN',
                    'apellidos' => 'CONDOR ULLOA',
                    'padre' => ['dni' => '43930125', 'nombres' => 'ELSEO AMOS', 'apellidos' => 'CONDOR PARRA', 'sexo' => 'M'],
                    'madre' => ['dni' => '43777556', 'nombres' => 'ROSMERY NELLY', 'apellidos' => 'ULLOA NATERES DE CONDOR', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91653879',
                    'nombres' => 'LIAM ETHAN',
                    'apellidos' => 'WERLEN BORDA',
                    'padre' => ['dni' => '45558722', 'nombres' => 'JOEL DAVID', 'apellidos' => 'WERLEN ARCE', 'sexo' => 'M'],
                    'madre' => ['dni' => '71202159', 'nombres' => 'ANDREA MILUSKA', 'apellidos' => 'BORDA CRISPIN', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91498422',
                    'nombres' => 'SERGIO ELIAN',
                    'apellidos' => 'LEON HERRERA',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '22670111', 'nombres' => 'GILBERT ALFREDO', 'apellidos' => 'LEON HUAMANI', 'sexo' => 'M'],
                    'madre' => ['dni' => '76637120', 'nombres' => 'YUSANA', 'apellidos' => 'HERRERA PEREZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91639897',
                    'nombres' => 'JUAN DIEGO',
                    'apellidos' => 'BARRAGAN CAMPOS',
                    'padre' => ['dni' => '41774654', 'nombres' => 'JUAN', 'apellidos' => 'BARRAGAN LOYOLA', 'sexo' => 'M'],
                    'madre' => ['dni' => '46542625', 'nombres' => 'VANESA', 'apellidos' => 'CAMPOS POCCO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91726810',
                    'nombres' => 'THIAGO JESUS',
                    'apellidos' => 'VILLA VALLADARES',
                    'observacion' => 'H-',
                    'padre' => ['dni' => '49590223', 'nombres' => 'JESUS', 'apellidos' => 'VILLA CRISOSTOMO', 'sexo' => 'M'],
                    'madre' => ['dni' => '43564829', 'nombres' => 'GUEYSI', 'apellidos' => 'VALLADARES HUARCAYA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90048670',
                    'nombres' => 'SHIANA (INTERNO)',
                    'apellidos' => 'BECERRA LEON',
                    'padre' => ['dni' => '20594775', 'nombres' => 'JULIO ARNALDO', 'apellidos' => 'BECERRA GARCIA', 'sexo' => 'M'],
                    'madre' => ['dni' => '40868842', 'nombres' => 'BETZABE OLENKA', 'apellidos' => 'LEON CORTIJO', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 2,
            'seccion' => 'A',
            'estudiantes' => [
                [
                    'dni' => '91214447',
                    'nombres' => 'ROMINA ARLETH',
                    'apellidos' => 'ALEGRIA ALVA',
                    'padre' => ['dni' => '10606920', 'nombres' => 'BORIS CARLOF', 'apellidos' => 'ALEGRIA CHAHUA', 'sexo' => 'M'],
                    'madre' => ['dni' => '44161619', 'nombres' => 'ROSA LILIANA', 'apellidos' => 'ALVA BAZAN', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91002309',
                    'nombres' => 'THEO LEAO',
                    'apellidos' => 'ATALAYA VASQUEZ',
                    'padre' => ['dni' => '46946825', 'nombres' => 'TELMO', 'apellidos' => 'ATALAYA CERNA', 'sexo' => 'M'],
                    'madre' => ['dni' => '45343307', 'nombres' => 'JUDITH', 'apellidos' => 'VASQUEZ CALERO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91044267',
                    'nombres' => 'ANTWONE BRAHIM',
                    'apellidos' => 'BORONDA CLAVIN',
                    'padre' => null,
                    'madre' => ['dni' => '71116379', 'nombres' => 'KARI GIOVANA', 'apellidos' => 'CLAVIN CORDOVA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90812119',
                    'nombres' => 'ARIANA NIKOL',
                    'apellidos' => 'CASTILLO LOPEZ',
                    'padre' => null,
                    'madre' => ['dni' => '46198363', 'nombres' => 'KATIUSKA', 'apellidos' => 'LOPEZ ARRIETA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90813942',
                    'nombres' => 'LINDSAY YANDI',
                    'apellidos' => 'CCASISCCA DE LA TORRE',
                    'padre' => ['dni' => '70261254', 'nombres' => 'JACK JASON', 'apellidos' => 'CCASISCCA MARTEL', 'sexo' => 'M'],
                    'madre' => ['dni' => '70261246', 'nombres' => 'ROSARIO YENIFFER', 'apellidos' => 'DE LA TORRE COLORADO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91229804',
                    'nombres' => 'ZAYRA ELIETTE',
                    'apellidos' => 'DIAZ BALLESTEROS',
                    'padre' => null,
                    'madre' => null,
                ],
                [
                    'dni' => '90779599',
                    'nombres' => 'KORAL GIANNA',
                    'apellidos' => 'FALCON HUAMAN',
                    'padre' => ['dni' => '72541910', 'nombres' => 'KEVIN', 'apellidos' => 'FALCON ORTIZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '71206106', 'nombres' => 'GRACIELA PAMELA', 'apellidos' => 'HUAMAN ALANIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90987131',
                    'nombres' => 'GAEL JOAQUIN',
                    'apellidos' => 'GALINDO HIDALGO',
                    'padre' => ['dni' => '46134705', 'nombres' => 'JOAQUIN', 'apellidos' => 'GALINDO SOTELO', 'sexo' => 'M'],
                    'madre' => ['dni' => '46753221', 'nombres' => 'LEILY', 'apellidos' => 'HIDALGO ORTIZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90999745',
                    'nombres' => 'FABRICIO ANDRE',
                    'apellidos' => 'MEZA RIVERA',
                    'padre' => ['dni' => '43361565', 'nombres' => 'WILBER JHON', 'apellidos' => 'MEZA SIGNORI', 'sexo' => 'M'],
                    'madre' => ['dni' => '43934656', 'nombres' => 'ROSARIO DEL PILAR', 'apellidos' => 'RIVERA CORDOVA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90776715',
                    'nombres' => 'SANTIAGO PAULO',
                    'apellidos' => 'NI' . "\u{00D1}" . 'O DE GUZMAN HUAMAN',
                    'padre' => null,
                    'madre' => ['dni' => '71206055', 'nombres' => 'YOSLI', 'apellidos' => 'HUAMAN ALANIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90853900',
                    'nombres' => 'CAMILA JENELLE',
                    'apellidos' => 'PAREDES DIEGO',
                    'observacion' => 'H',
                    'padre' => ['dni' => '70255134', 'nombres' => 'ELVIS ALEJANDRO', 'apellidos' => 'PAREDES ROMERO', 'sexo' => 'M'],
                    'madre' => ['dni' => '47179088', 'nombres' => 'RODE', 'apellidos' => 'DIEGO DE LA CRUZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90856828',
                    'nombres' => 'MICIELO VALENTINA MELISSA',
                    'apellidos' => 'SALAZAR DE LA CRUZ',
                    'padre' => ['dni' => '44949296', 'nombres' => 'SIONI EDISON', 'apellidos' => 'SALAZAR RAMOS', 'sexo' => 'M'],
                    'madre' => ['dni' => '45094049', 'nombres' => 'MARLYN MELISSA', 'apellidos' => 'DE LA CRUZ JURADO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91167904',
                    'nombres' => 'DANIELA SHANDEL',
                    'apellidos' => 'TARAZONA LAVADO',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '42338047', 'nombres' => 'CARLOS ALBERTO', 'apellidos' => 'TARAZONA JANAMPA', 'sexo' => 'M'],
                    'madre' => ['dni' => '42852590', 'nombres' => 'ADA AGNELA', 'apellidos' => 'LAVADO CONTRERAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90894031',
                    'nombres' => 'SEBASTIAN LUIS',
                    'apellidos' => 'TARAZONA MAYTA',
                    'padre' => ['dni' => null, 'nombres' => 'JHOEL BENJAMIN', 'apellidos' => 'TARAZONA SOTO', 'sexo' => 'M'],
                    'madre' => ['dni' => '70255123', 'nombres' => 'YESMILA NAYRA', 'apellidos' => 'MAYTA ENRIQUEZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90831112',
                    'nombres' => 'SEBASTIAN VALENTINO',
                    'apellidos' => 'TRINIDAD HUAMAN',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '43095494', 'nombres' => 'JOEL DANTE', 'apellidos' => 'TRINIDAD QUISPE', 'sexo' => 'M'],
                    'madre' => ['dni' => '45892022', 'nombres' => 'LUZMILA ZORAIDA', 'apellidos' => 'HUAMAN ALANIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91270192',
                    'nombres' => 'JOSE MANUEL',
                    'apellidos' => 'VARGAS ALARCON',
                    'padre' => ['dni' => '43494035', 'nombres' => 'JOSE LUIS', 'apellidos' => 'VARGAS GUTARRA', 'sexo' => 'M'],
                    'madre' => ['dni' => '44761039', 'nombres' => 'ISABEL STEPHANI', 'apellidos' => 'ALARCON PORTILLO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90730257',
                    'nombres' => 'FARIT ELISEO',
                    'apellidos' => 'VIVANCO URRUTIA',
                    'padre' => null,
                    'madre' => ['dni' => '77694987', 'nombres' => 'MILUSCA', 'apellidos' => 'URRUTIA GOMEZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91227610',
                    'nombres' => 'ANGEL MANUEL',
                    'apellidos' => 'CABALLERO CORONEL',
                    'padre' => ['dni' => '07925718', 'nombres' => 'MANUEL ALEJANDRO', 'apellidos' => 'CABALLERO NORIEGA', 'sexo' => 'M'],
                    'madre' => ['dni' => '42617746', 'nombres' => 'CRISTINA OLIVIA', 'apellidos' => 'CORONEL MEZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90869997',
                    'nombres' => 'MATHIAS ISAAC',
                    'apellidos' => 'ROJAS HUAMAN',
                    'padre' => ['dni' => '04074092', 'nombres' => 'MARIO', 'apellidos' => 'ROJAS BASUALDO', 'sexo' => 'M'],
                    'madre' => ['dni' => '40019158', 'nombres' => 'ALEJANDRINA', 'apellidos' => 'HUAMAN QUISPE DE ROJAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90823475',
                    'nombres' => 'ARIANA',
                    'apellidos' => 'ARRUNATEGUI PAIS',
                    'padre' => ['dni' => null, 'nombres' => 'GERARDO', 'apellidos' => 'ARRUNATEGUI COTRINA', 'sexo' => 'M'],
                    'madre' => ['dni' => '41222408', 'nombres' => 'PERY LUZ', 'apellidos' => 'PAIS SUCNO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91220252',
                    'nombres' => 'ANTHONY SEBASTIAN',
                    'apellidos' => 'SILVA ALBERTO',
                    'observacion' => 'H+',
                    'padre' => ['dni' => null, 'nombres' => 'IOSUE ANTONY', 'apellidos' => 'SILVA CHALLCO', 'sexo' => 'M'],
                    'madre' => ['dni' => null, 'nombres' => 'FIORELA BEATRIZ', 'apellidos' => 'ALBERTO VALLE', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 2,
            'seccion' => 'B',
            'estudiantes' => [
                [
                    'dni' => '91299700',
                    'nombres' => 'LUNA (INTERNO)',
                    'apellidos' => 'BRITO COLL CARDENAS',
                    'padre' => ['dni' => null, 'nombres' => 'PABLO MAURICIS', 'apellidos' => 'BRITO ALIA', 'sexo' => 'M'],
                    'madre' => ['dni' => '45091160', 'nombres' => 'LIZA PAULA FIORELLA', 'apellidos' => 'COLL CARDENAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91055944',
                    'nombres' => 'LUCIANO ENRIQUE',
                    'apellidos' => 'CONDE USCUVILCA',
                    'padre' => null,
                    'madre' => ['dni' => '48448581', 'nombres' => 'ANANI', 'apellidos' => 'USCUVILCA HUAYLAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91236981',
                    'nombres' => 'EMILY SOFIA',
                    'apellidos' => 'DURAND RODRIGUEZ',
                    'padre' => ['dni' => '10665140', 'nombres' => 'DAVID CARLOS', 'apellidos' => 'DURAND AGUILAR', 'sexo' => 'M'],
                    'madre' => ['dni' => '44501092', 'nombres' => 'ARACELI EUSTASIA', 'apellidos' => 'RODRIGUEZ CONTRERAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91186738',
                    'nombres' => 'LIDSNEL EVANYELIN',
                    'apellidos' => 'PARDO CAJAS',
                    'padre' => ['dni' => '71470025', 'nombres' => 'AMADO FERNANDO', 'apellidos' => 'PARDO HUARANGA', 'sexo' => 'M'],
                    'madre' => ['dni' => '72044842', 'nombres' => 'BRIGHIT NAYELY', 'apellidos' => 'CAJAS BAZAN', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91117007',
                    'nombres' => 'ABRAHAN NIAN',
                    'apellidos' => 'RAFAELO GARCIA',
                    'padre' => ['dni' => '42592526', 'nombres' => 'VLADIMIR ABIMAEL', 'apellidos' => 'RAFAELO ESPINOZA', 'sexo' => 'M'],
                    'madre' => ['dni' => '45656345', 'nombres' => 'YESICA ENDIRA', 'apellidos' => 'GARCIA ZELADA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90208761',
                    'nombres' => 'JHON PIERO (INTERNO)',
                    'apellidos' => 'FLORES PALOMINO',
                    'padre' => ['dni' => '04339390', 'nombres' => 'PEDRO FRANCISCO', 'apellidos' => 'FLORES GONZALES', 'sexo' => 'M'],
                    'madre' => ['dni' => '04341931', 'nombres' => 'NELLY SONIA', 'apellidos' => 'PALOMINO CORDOVA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90644954',
                    'nombres' => 'ABRAHAM MATEO (INTERNO)',
                    'apellidos' => 'MARCHAND PICSE',
                    'padre' => ['dni' => '45614833', 'nombres' => 'CAMILA', 'apellidos' => 'RICSE HUAYNALAYA', 'sexo' => 'F'],
                    'madre' => ['dni' => '73376914', 'nombres' => 'CANDY LISSETH', 'apellidos' => 'TIA-TELLO SALCEDO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90621970',
                    'nombres' => 'ALEJANDRA (INTERNO)',
                    'apellidos' => 'SALCEDO GUISADO',
                    'padre' => null,
                    'madre' => ['dni' => '43000859', 'nombres' => 'MARIA ELISABETH', 'apellidos' => 'GUIZADO SINCHI', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90460451',
                    'nombres' => 'EMANUEL (INTERNO)',
                    'apellidos' => 'SOSA HURTADO',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '42187175', 'nombres' => 'EDER FELIX', 'apellidos' => 'SOSA TOLENTINO', 'sexo' => 'M'],
                    'madre' => ['dni' => '42935435', 'nombres' => 'RUTH', 'apellidos' => 'HURTADO LOAYZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90867608',
                    'nombres' => 'THAISA ROMINA',
                    'apellidos' => 'RIVERA ROSALES',
                    'padre' => ['dni' => '72754656', 'nombres' => 'LESLY ROSANNY', 'apellidos' => 'ROSALES GARCIA', 'sexo' => null],
                    'madre' => null,
                ],
                [
                    'dni' => '90876616',
                    'nombres' => 'MAYKEL SANTIAGO',
                    'apellidos' => 'QUINCHO RUIZ',
                    'padre' => ['dni' => '48426267', 'nombres' => 'CERAPIO SANTIAGO', 'apellidos' => 'QUINCHO URETA', 'sexo' => 'M'],
                    'madre' => ['dni' => '72110718', 'nombres' => 'RUTH ELIZABETH', 'apellidos' => 'RUIZ GOMEZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90575438',
                    'nombres' => 'LIAM KURT (3ER GRADO INTERNO)',
                    'apellidos' => 'LA TORRE ORTIZ',
                    'padre' => ['dni' => '43179758', 'nombres' => 'KURTY JAMENSON', 'apellidos' => 'LA TORRE ALVAREZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '71204573', 'nombres' => 'SARITA PASCUALA', 'apellidos' => 'ORTIZ BARRAZUETA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91094943',
                    'nombres' => 'LUCAS CAYETANO',
                    'apellidos' => 'CALLAN FLORES',
                    'padre' => ['dni' => '46811455', 'nombres' => 'BENIGNO', 'apellidos' => 'CALLAN ORNETA', 'sexo' => 'M'],
                    'madre' => ['dni' => '70271530', 'nombres' => 'LEIDY LIZBETH', 'apellidos' => 'FLORES GARCIA', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 3,
            'seccion' => 'A',
            'estudiantes' => [
                [
                    'dni' => '90196139',
                    'nombres' => 'ROUSS CLEER',
                    'apellidos' => 'AEDO MENESES',
                    'padre' => ['dni' => '44413170', 'nombres' => 'DANTE ALEXANDER', 'apellidos' => 'AEDO VILLAGARAY', 'sexo' => 'M'],
                    'madre' => ['dni' => '46949384', 'nombres' => 'MARIZA FLORIZA', 'apellidos' => 'MENESES VILLANUEVA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90206751',
                    'nombres' => 'LUCIANA MAZIEL',
                    'apellidos' => 'ALBENGRIN ESTELA',
                    'padre' => ['dni' => '40093760', 'nombres' => 'DENIS WILTON', 'apellidos' => 'ALBENGRIN GARCIA', 'sexo' => 'M'],
                    'madre' => ['dni' => '73997521', 'nombres' => 'EVELEN', 'apellidos' => 'ESTELA RAMOS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90356622',
                    'nombres' => 'FLAVIA VALERIA',
                    'apellidos' => 'BLANCO ALIAGA',
                    'padre' => null,
                    'madre' => ['dni' => '40841086', 'nombres' => 'JANET CIDALIA', 'apellidos' => 'ALIAGA ORELLANA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90249888',
                    'nombres' => 'NAELA DELIA',
                    'apellidos' => 'CHAVEZ HURTADO',
                    'padre' => null,
                    'madre' => ['dni' => '10332517', 'nombres' => 'IGNACIA', 'apellidos' => 'HURTADO ALDAVA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90545309',
                    'nombres' => 'KATALEIA EDITH',
                    'apellidos' => 'DE LA TORRE GALLARDO',
                    'padre' => ['dni' => '48705945', 'nombres' => 'MILOS FILIMON', 'apellidos' => 'DE LA TORRE PAREDES', 'sexo' => 'M'],
                    'madre' => ['dni' => '72611486', 'nombres' => 'BRENDA STEFANI', 'apellidos' => 'GALLARDO HUASQUI', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90677007',
                    'nombres' => 'CATALEYA',
                    'apellidos' => 'ECHEANDIA HUAMAN',
                    'padre' => ['dni' => '42208385', 'nombres' => 'SAMUEL', 'apellidos' => 'ECHEANDIA OJEDA', 'sexo' => 'M'],
                    'madre' => ['dni' => '46727679', 'nombres' => 'NORMA', 'apellidos' => 'HUAMAN ALANIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90383747',
                    'nombres' => 'FLAVIA STANA',
                    'apellidos' => 'LOYA VALTA',
                    'padre' => ['dni' => '41622213', 'nombres' => 'LUIS GUSTAVO', 'apellidos' => 'LOYA GUSTAVSON', 'sexo' => 'M'],
                    'madre' => ['dni' => '04307483', 'nombres' => 'SANDRA FABIOLA', 'apellidos' => 'YALTA LA TORRE', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90621729',
                    'nombres' => 'YARETZI MIHEYSU',
                    'apellidos' => 'RENGIFO SAYA',
                    'padre' => ['dni' => '42584075', 'nombres' => 'HECTOR FREDY', 'apellidos' => 'RENGIFO ROWE', 'sexo' => 'M'],
                    'madre' => ['dni' => '71667033', 'nombres' => 'CELIA SUSY', 'apellidos' => 'SAYA YURIVILCA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90537518',
                    'nombres' => 'MADISON DEL ROCIO',
                    'apellidos' => 'SALAZAR REQUEJO',
                    'padre' => ['dni' => '40026572', 'nombres' => 'KEVIN', 'apellidos' => 'SALAZAR AMES', 'sexo' => 'M'],
                    'madre' => ['dni' => '44546209', 'nombres' => 'MIRIAM ROCIO', 'apellidos' => 'REQUEJO MEJIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90523556',
                    'nombres' => 'SAMIRA KIARA',
                    'apellidos' => 'TARAZONA DIEGO',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '43205649', 'nombres' => 'RULO LINCOLN', 'apellidos' => 'TARAZONA JANAMPA', 'sexo' => 'M'],
                    'madre' => ['dni' => '77247628', 'nombres' => 'CRIS FLOR', 'apellidos' => 'DIEGO AQUINO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90550912',
                    'nombres' => 'MIA CHARLOTTE',
                    'apellidos' => 'VELASCO BERNARDO',
                    'padre' => ['dni' => '47329419', 'nombres' => 'DIETER ALBERT', 'apellidos' => 'VELASCO PAREDES', 'sexo' => 'M'],
                    'madre' => ['dni' => '74316192', 'nombres' => 'LAURA HILARITH BETZABE', 'apellidos' => 'BERNARDO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90401832',
                    'nombres' => 'DARLETH ROSARIO',
                    'apellidos' => 'YALICO ESTEBAN',
                    'padre' => ['dni' => null, 'nombres' => 'ROLANDO', 'apellidos' => 'MICHE GARCIA', 'sexo' => 'M'],
                    'madre' => ['dni' => '40058024', 'nombres' => 'CHARO VIOLETA', 'apellidos' => 'ESTEBAN OSORIO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90041642',
                    'nombres' => 'XIOMARA (INTERNO)',
                    'apellidos' => 'MASGO BLAS',
                    'padre' => null,
                    'madre' => ['dni' => '43649999', 'nombres' => 'KELLY', 'apellidos' => 'BLAS ESPINOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90499483',
                    'nombres' => 'ALMENDRA KASANDRA',
                    'apellidos' => 'MELGAREJO BESARES',
                    'padre' => ['dni' => '10539175', 'nombres' => 'AMILCAR', 'apellidos' => 'MELGAREJO VERDE', 'sexo' => 'M'],
                    'madre' => null,
                ],
                [
                    'dni' => '90658567',
                    'nombres' => 'BRIANA JESIAH',
                    'apellidos' => 'CASTRO GOMEZ',
                    'padre' => ['dni' => '41144473', 'nombres' => 'JUAN HUGO', 'apellidos' => 'CASTRO CUETO', 'sexo' => 'M'],
                    'madre' => ['dni' => '41119933', 'nombres' => 'MARIA ELENA', 'apellidos' => 'GOMEZ LLOCCLLA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90558226',
                    'nombres' => 'RAFAELLA',
                    'apellidos' => 'SAEZ VERDE',
                    'padre' => null,
                    'madre' => ['dni' => '43835724', 'nombres' => 'MILAGROS', 'apellidos' => 'VERDE LA TORRE', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 3,
            'seccion' => 'B',
            'estudiantes' => [
                [
                    'dni' => '79776663',
                    'nombres' => 'RALL KRLOS (INTERNO)',
                    'apellidos' => 'ALBENGRIN CASIMIRO',
                    'padre' => ['dni' => '04338957', 'nombres' => 'KOEL CARLOS ALBERTO', 'apellidos' => 'ALBENGRIN', 'sexo' => 'M'],
                    'madre' => ['dni' => '44248752', 'nombres' => 'YOLINHA', 'apellidos' => 'CASIMIRO ZEVALLOS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90655557',
                    'nombres' => 'JOAQUIN ENRIQUE',
                    'apellidos' => 'CHIRINOS DE LA CUBA',
                    'padre' => ['dni' => '16688754', 'nombres' => 'JORGE ENRIQUE', 'apellidos' => 'CHIRINOS CASTRO', 'sexo' => 'M'],
                    'madre' => ['dni' => '42826005', 'nombres' => 'CAROL ARLETTE', 'apellidos' => 'DE LA CUBA QUISPE', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90526730',
                    'nombres' => 'ABBI GUADALUPE',
                    'apellidos' => 'OLIVERA FLORES',
                    'padre' => ['dni' => '21244856', 'nombres' => 'HUMBERTO VIDAL', 'apellidos' => 'ABUELO-FLORES POVES', 'sexo' => 'M'],
                    'madre' => ['dni' => '43934646', 'nombres' => 'MELIZA NATHALY', 'apellidos' => 'FLORES HUANAY', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90301112',
                    'nombres' => 'AMY VALENTINA',
                    'apellidos' => 'PINO GARCIA',
                    'padre' => ['dni' => '43095489', 'nombres' => 'JORGE LUIS', 'apellidos' => 'PINO HUAMAN', 'sexo' => 'M'],
                    'madre' => ['dni' => '48184330', 'nombres' => 'FAVIOLA FIORELLA', 'apellidos' => 'GARCIA ALEGRE', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90520601',
                    'nombres' => 'MILAGRITOS FRANCHESKA',
                    'apellidos' => 'RICAPA ESPINOZA',
                    'padre' => ['dni' => '48601821', 'nombres' => 'FRANK ROBERTH', 'apellidos' => 'RICAPA ESPINOZA', 'sexo' => 'M'],
                    'madre' => ['dni' => '43632408', 'nombres' => 'MILAGROS CARINA', 'apellidos' => 'ESPINOZA FLORES', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90399752',
                    'nombres' => 'ELIAN ALESSANDRO',
                    'apellidos' => 'SCHLEGEL RAYMUNDO',
                    'observacion' => 'H-',
                    'padre' => ['dni' => '70795247', 'nombres' => 'JOSUE NEMIAS', 'apellidos' => 'CARHUA ROSALES', 'sexo' => 'M'],
                    'madre' => ['dni' => '70609122', 'nombres' => 'FRITZA PAMELA', 'apellidos' => 'RAYMUNDO SANDOVAL', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90133156',
                    'nombres' => 'YADIEL DILAN',
                    'apellidos' => 'MONTESINO CALDERON',
                    'padre' => null,
                    'madre' => ['dni' => '43254236', 'nombres' => 'EVA LISBETH', 'apellidos' => 'CALDERON GOMEZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90262252',
                    'nombres' => 'ALBERTH NILTON',
                    'apellidos' => 'TOVAR ROJAS',
                    'padre' => ['dni' => '06797676', 'nombres' => 'NILTON WILFREDO', 'apellidos' => 'TOVAR ZARATE', 'sexo' => 'M'],
                    'madre' => ['dni' => '43063571', 'nombres' => 'LILIANA MARGOT', 'apellidos' => 'ROJAS ESTRADA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '91713108',
                    'nombres' => 'DAMARIS AYLEN',
                    'apellidos' => 'RIVERA LANDA',
                    'padre' => ['dni' => '41726471', 'nombres' => 'GUSTAVO ALBERTO', 'apellidos' => 'RIVERA PE' . "\u{00D1}" . 'A', 'sexo' => 'M'],
                    'madre' => ['dni' => '47213295', 'nombres' => 'FIORELA NARCISA', 'apellidos' => 'LANDA SOTO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90367083',
                    'nombres' => 'ZHAIR',
                    'apellidos' => 'SOTELO MARTINEZ',
                    'padre' => ['dni' => '80653399', 'nombres' => 'KARENINA CARMISY', 'apellidos' => 'ABUELA-MUCHA INGA', 'sexo' => 'F'],
                    'madre' => ['dni' => '75669067', 'nombres' => 'JOANNE YOSSILU', 'apellidos' => 'MARTINEZ MUCHA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90040470',
                    'nombres' => 'ALLPA KALLPA (INTERNO)',
                    'apellidos' => 'BRITO COLL CARDENAS',
                    'observacion' => 'H+',
                    'padre' => ['dni' => null, 'nombres' => 'MAURICIS', 'apellidos' => 'BRITO ALIA', 'sexo' => 'M'],
                    'madre' => ['dni' => '45091160', 'nombres' => 'LIZA PAULA FIORELLA', 'apellidos' => 'COLL CARDENAS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79383704',
                    'nombres' => 'RHAMI (INTERNO)',
                    'apellidos' => 'LUNA FARFAN',
                    'padre' => ['dni' => null, 'nombres' => 'RICARDO ANTONIO', 'apellidos' => 'LUNA DIAZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '41768089', 'nombres' => 'ANGELA', 'apellidos' => 'FARFAN VERDE', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90313057',
                    'nombres' => 'VICTORIA',
                    'apellidos' => 'CASIMIRO MICHE',
                    'padre' => ['dni' => '04322281', 'nombres' => 'CESAR', 'apellidos' => 'CASIMIRO PALOMINO', 'sexo' => 'M'],
                    'madre' => ['dni' => '70254042', 'nombres' => 'DORA JULIANA', 'apellidos' => 'MICHI BALARIN', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90575207',
                    'nombres' => 'HIEROLD ESTEVEN',
                    'apellidos' => 'JURADO DE LA CRUZ',
                    'padre' => ['dni' => '72284107', 'nombres' => 'ABDIAS', 'apellidos' => 'JURADO ESTEBAN', 'sexo' => 'M'],
                    'madre' => ['dni' => '72284107', 'nombres' => 'BETSA', 'apellidos' => 'DE LA CRUZ MALPARTIDA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90603722',
                    'nombres' => 'KLARISSE RAFAELA',
                    'apellidos' => 'TRINIDAD ATAHUAMAN',
                    'padre' => null,
                    'madre' => ['dni' => '71689273', 'nombres' => 'ANGELA PAOLA', 'apellidos' => 'ATAHUAMAN MACHACUAY', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 4,
            'seccion' => 'A',
            'estudiantes' => [
                [
                    'dni' => '79732591',
                    'nombres' => 'OSLED DANAE',
                    'apellidos' => 'CANO VICENTE',
                    'padre' => ['dni' => '71202352', 'nombres' => 'CRISTHIAN JORDAN', 'apellidos' => 'CANO ALBENGRIN', 'sexo' => 'M'],
                    'madre' => ['dni' => '70781323', 'nombres' => 'TREISSY KATHERINE', 'apellidos' => 'VICENTE MENDOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90012663',
                    'nombres' => 'YOIS',
                    'apellidos' => 'GONZALES PEREZ',
                    'padre' => ['dni' => '04341589', 'nombres' => 'JAVIER TOMAS', 'apellidos' => 'GONZALES CLAVO', 'sexo' => 'M'],
                    'madre' => ['dni' => '22308969', 'nombres' => 'SUSANA CAROLINA', 'apellidos' => 'PEREZ FLORES', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79791318',
                    'nombres' => 'RICHARD ADRIANO',
                    'apellidos' => 'GONZALES VERDE',
                    'padre' => ['dni' => '25861644', 'nombres' => 'RICHARD DUBER', 'apellidos' => 'GONZALES CALIXTO', 'sexo' => 'M'],
                    'madre' => ['dni' => '41021330', 'nombres' => 'CLAUDIA', 'apellidos' => 'VERDE YALTA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79723121',
                    'nombres' => 'MATHIAS SEBASTIAN',
                    'apellidos' => 'GUEVARA AYALA',
                    'padre' => ['dni' => '80132521', 'nombres' => 'MANUEL', 'apellidos' => 'GUEVARA AMEZAGA', 'sexo' => 'M'],
                    'madre' => ['dni' => '45609432', 'nombres' => 'KATHERINE XENIA', 'apellidos' => 'AYALA GARCIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79644456',
                    'nombres' => 'JORGE DANIEL',
                    'apellidos' => 'MEDRANO PE' . "\u{00D1}" . 'A',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '42699559', 'nombres' => 'DANIEL ENRIQUE', 'apellidos' => 'MEDRANO ORIZANO', 'sexo' => 'M'],
                    'madre' => ['dni' => '43336170', 'nombres' => 'ROSA ELIZABETH', 'apellidos' => 'PE' . "\u{00D1}" . 'A MENDIZABAL', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90117101',
                    'nombres' => 'ALEJANDRO GAEL',
                    'apellidos' => 'MENDOZA TEJADA',
                    'padre' => ['dni' => '70458526', 'nombres' => 'JOSE ALFREDO', 'apellidos' => 'MENDOZA KRAUCHNER', 'sexo' => 'M'],
                    'madre' => ['dni' => '70261314', 'nombres' => 'GABRIELA DEL PILAR', 'apellidos' => 'TEJADA DE LA CUBA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79756399',
                    'nombres' => 'TANNER BENYAMIN',
                    'apellidos' => 'MERCADO ACOSTA',
                    'padre' => null,
                    'madre' => ['dni' => '42121102', 'nombres' => 'GINA ELIDA', 'apellidos' => 'ACOSTA LAZARO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90043884',
                    'nombres' => 'BRITHANY LESLY',
                    'apellidos' => 'MERCADO ROMERO',
                    'padre' => null,
                    'madre' => ['dni' => '04303125', 'nombres' => 'MARIA ANGELICA', 'apellidos' => 'ESPINOZA DE MERCADO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79713804',
                    'nombres' => 'STEVE ALEJANDRO',
                    'apellidos' => 'PAREDES DIEGO',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '70255134', 'nombres' => 'ELVIS ALEJANDRO', 'apellidos' => 'PAREDES ROMERO', 'sexo' => 'M'],
                    'madre' => ['dni' => '47179088', 'nombres' => 'RODE', 'apellidos' => 'DIEGO DE LA CRUZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79671551',
                    'nombres' => 'VALENTINA VICTORIA',
                    'apellidos' => 'RAMOS FERNANDEZ',
                    'padre' => ['dni' => '04301751', 'nombres' => 'JUAN DE DIOS', 'apellidos' => 'RAMOS TRUJILLO', 'sexo' => 'M'],
                    'madre' => ['dni' => '47595050', 'nombres' => 'JULY ALMENDRA', 'apellidos' => 'FERNANDEZ ALIAGA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79680613',
                    'nombres' => 'THIANA CAOBA',
                    'apellidos' => 'VARGAS IZQUIERDO',
                    'padre' => ['dni' => '40635961', 'nombres' => 'FREDDY', 'apellidos' => 'VARGAS QUIJADA', 'sexo' => 'M'],
                    'madre' => ['dni' => '43195642', 'nombres' => 'KATHERIN', 'apellidos' => 'IZQUIERDO ESTRELLA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90121453',
                    'nombres' => 'KENJI NOE',
                    'apellidos' => 'DE LA CRUZ CHINCHAY',
                    'padre' => ['dni' => '42395595', 'nombres' => 'EDWIN NOE', 'apellidos' => 'DE LA CRUZ MALPARTIDA', 'sexo' => 'M'],
                    'madre' => null,
                ],
                [
                    'dni' => '79915441',
                    'nombres' => 'MIKELY GERMAYORY',
                    'apellidos' => 'EGG QUEREVALU',
                    'padre' => ['dni' => '70221618', 'nombres' => 'JOHAN ANGEL', 'apellidos' => 'EGG LA TORRE', 'sexo' => 'M'],
                    'madre' => ['dni' => '77869876', 'nombres' => 'KAROL MARYORY', 'apellidos' => 'QUEREVALU ARBOLEDA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79899528',
                    'nombres' => 'DYLAND JHADYEL',
                    'apellidos' => 'MIJA RAMON',
                    'padre' => ['dni' => '44679886', 'nombres' => 'ANNER JHONY', 'apellidos' => 'MIJA RIVERA', 'sexo' => 'M'],
                    'madre' => ['dni' => '47734347', 'nombres' => 'YUDIT YANINA', 'apellidos' => 'RAMON MARCELO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79973356',
                    'nombres' => 'LIAN ADRIAN',
                    'apellidos' => 'REQUEJO MORALES',
                    'padre' => ['dni' => '70790246', 'nombres' => 'ROBER', 'apellidos' => 'REQUEJO GUEVARA', 'sexo' => 'M'],
                    'madre' => ['dni' => '46019059', 'nombres' => 'MIRIAM ROXANA', 'apellidos' => 'MORALES FERNANDEZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79708326',
                    'nombres' => 'THIAGO JOHANN',
                    'apellidos' => 'CRESPO ABAD',
                    'padre' => null,
                    'madre' => ['dni' => '71211056', 'nombres' => 'JUDYD TAVITA', 'apellidos' => 'ABAD REYNOSO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79982787',
                    'nombres' => 'ALEXANDER',
                    'apellidos' => 'CANALES CASTANEDA',
                    'padre' => ['dni' => '42084301', 'nombres' => 'JACK POLH', 'apellidos' => 'CANALES SOLIS', 'sexo' => 'M'],
                    'madre' => ['dni' => '41424137', 'nombres' => 'ENMA', 'apellidos' => 'CASTANEDA OYARCE', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 4,
            'seccion' => 'B',
            'estudiantes' => [
                [
                    'dni' => '90026836',
                    'nombres' => 'JUAN DARWIN',
                    'apellidos' => 'CARDENAS FARFAN',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '44925245', 'nombres' => 'DARWIN JERISH', 'apellidos' => 'CARDENAS ALCANTARA', 'sexo' => 'M'],
                    'madre' => ['dni' => null, 'nombres' => 'AMANDA MAGALY', 'apellidos' => 'FARFAN PIAVIZAN', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90066292',
                    'nombres' => 'MATTEO ALONSO',
                    'apellidos' => 'ESPINOZA PALERMO',
                    'padre' => ['dni' => '41899234', 'nombres' => 'RUBEN', 'apellidos' => 'ESPINOZA CALDERON', 'sexo' => 'M'],
                    'madre' => ['dni' => '44552154', 'nombres' => 'MILAGROS ROCIO', 'apellidos' => 'PALERMO MEDINA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90026792',
                    'nombres' => 'MATT CESAR',
                    'apellidos' => 'GUERRERO VERDE',
                    'estado_matricula' => Matricula::ESTADO_RETIRADA,
                    'padre' => null,
                    'madre' => ['dni' => '71266539', 'nombres' => 'VALERIA JANETH', 'apellidos' => 'VERDE YALTA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79705045',
                    'nombres' => 'BENJAMIN SALVADOR',
                    'apellidos' => 'IZQUIERDO HUAMAN',
                    'padre' => ['dni' => '40786486', 'nombres' => 'OSCAR DICK', 'apellidos' => 'IZQUIERDO ESTRELLA', 'sexo' => 'M'],
                    'madre' => ['dni' => '46576213', 'nombres' => 'MELISA DEYSI', 'apellidos' => 'HUAMAN LA TORRE', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79770341',
                    'nombres' => 'CAMILA ELIF',
                    'apellidos' => 'MOROCHO CHAPETA',
                    'padre' => ['dni' => '45419502', 'nombres' => 'ISAI', 'apellidos' => 'MOROCHO SOTO', 'sexo' => 'M'],
                    'madre' => ['dni' => '46310145', 'nombres' => 'BETSY RAQUEL', 'apellidos' => 'CHAPETA ANGO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79634507',
                    'nombres' => 'JOHANNES GILBERTH',
                    'apellidos' => 'NOSSING BALLESTEROS',
                    'padre' => null,
                    'madre' => ['dni' => '10276029', 'nombres' => 'HAYDEE IRMA', 'apellidos' => 'BALLESTEROS RIOS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79728749',
                    'nombres' => 'ROSE CORY',
                    'apellidos' => 'ORIHUELA POLINAR',
                    'padre' => null,
                    'madre' => ['dni' => '63069116', 'nombres' => 'MERY', 'apellidos' => 'POLINAR CARHUARICRA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79971382',
                    'nombres' => 'ALICE MAIA',
                    'apellidos' => 'SCHIPPER ACOSTA',
                    'padre' => ['dni' => '41054266', 'nombres' => 'ADOLF RICHARD', 'apellidos' => 'SCHIPPER ALCANTARA', 'sexo' => 'M'],
                    'madre' => ['dni' => '77227231', 'nombres' => 'ERIKA GERALDINE', 'apellidos' => 'CESPEDES ACOSTA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79928303',
                    'nombres' => 'MATHIAS ANTON',
                    'apellidos' => 'TARAZONA HUERTA',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '46002628', 'nombres' => 'OSCAR OSWALDO', 'apellidos' => 'TARAZONA JANAMPA', 'sexo' => 'M'],
                    'madre' => ['dni' => '44161600', 'nombres' => 'ANA MARIA', 'apellidos' => 'HUERTA OLLERO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79929752',
                    'nombres' => 'CRISTIAN ERNESTO',
                    'apellidos' => 'VELITA CARBAJAL',
                    'padre' => null,
                    'madre' => ['dni' => '41831906', 'nombres' => 'CRISTINA CECILIA', 'apellidos' => 'CARBAJAL ESLAVA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78087426',
                    'nombres' => 'MATHIAS FABIO (INTERNO)',
                    'apellidos' => 'CHAMORRO CANCHO',
                    'padre' => ['dni' => '45908802', 'nombres' => 'HUBERT IVAN', 'apellidos' => 'CHAMORRO VICTORIO', 'sexo' => 'M'],
                    'madre' => ['dni' => '42697260', 'nombres' => 'JESSI CINTHIA', 'apellidos' => 'CANCHO ALVARO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79859783',
                    'nombres' => 'BRUNO GABRIEL',
                    'apellidos' => 'DIAZ WITTING',
                    'padre' => ['dni' => '09865707', 'nombres' => 'BRUNO DARIO', 'apellidos' => 'DIAZ ABANTO', 'sexo' => 'M'],
                    'madre' => ['dni' => '43728136', 'nombres' => 'KARIN LISBETH', 'apellidos' => 'WITTING GSTIR', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79829367',
                    'nombres' => 'JEFFERSON GILBERT',
                    'apellidos' => 'LEON HERRERA',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '22670111', 'nombres' => 'GILBERT ALFREDO', 'apellidos' => 'LEON HUAMANI', 'sexo' => 'M'],
                    'madre' => ['dni' => '76637120', 'nombres' => 'YUSANA', 'apellidos' => 'HERRERA PEREZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79790961',
                    'nombres' => 'ASTRID CAROLINA',
                    'apellidos' => 'BOTGER ALEGRE',
                    'padre' => ['dni' => '42675037', 'nombres' => 'JOSE DAVID', 'apellidos' => 'B' . "\u{00D6}" . 'TTGER NANO', 'sexo' => 'M'],
                    'madre' => ['dni' => '40668700', 'nombres' => 'ERIKA CINTYA', 'apellidos' => 'ALEGRE CANTALICIO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90015616',
                    'nombres' => 'ANDREA MICHELLE',
                    'apellidos' => 'EFOS ZARATE',
                    'observacion' => 'H',
                    'padre' => ['dni' => '10882755', 'nombres' => 'LUIS ALBERTO', 'apellidos' => 'EFOS PEREZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '44263961', 'nombres' => 'ELSA ADELINDA', 'apellidos' => 'ZARATE PEREZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '90113137',
                    'nombres' => 'LIAM ABDIEL',
                    'apellidos' => 'LLALLAHUI HIDALGO',
                    'observacion' => 'H',
                    'padre' => ['dni' => null, 'nombres' => 'ADOLFO GUSTAVO', 'apellidos' => 'LLALLAHUI CALLA' . "\u{00D1}" . 'AUPA', 'sexo' => 'M'],
                    'madre' => ['dni' => '48187931', 'nombres' => 'SUSAN', 'apellidos' => 'HIDALGO ORTIZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79359385',
                    'nombres' => 'ANGIE LIZETH',
                    'apellidos' => 'BARROS ARAPA',
                    'padre' => ['dni' => '08660171', 'nombres' => 'ELVIS', 'apellidos' => 'BARROS GUTIERREZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '10245561', 'nombres' => 'GABY', 'apellidos' => 'ARAPA SANCA', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 5,
            'seccion' => 'A',
            'estudiantes' => [
                [
                    'dni' => '79358311',
                    'nombres' => 'AARON ALCE',
                    'apellidos' => 'ALDAVA BORONDA',
                    'padre' => ['dni' => '43508310', 'nombres' => 'RUBEN', 'apellidos' => 'ALDAVA POMAZONGO', 'sexo' => 'M'],
                    'madre' => ['dni' => '42252349', 'nombres' => 'KAREN SOLINDA', 'apellidos' => 'BORONDA CARBAJAL', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79434186',
                    'nombres' => 'RAYKO KENET',
                    'apellidos' => 'ARIAS PALOMINO',
                    'padre' => ['dni' => '71852582', 'nombres' => 'JEFFREY KENT', 'apellidos' => 'ARIAS VERDE', 'sexo' => 'M'],
                    'madre' => ['dni' => '71268806', 'nombres' => 'XIOMARA DEISY', 'apellidos' => 'PALOMINO CURI', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79395195',
                    'nombres' => 'RAFAELA TIARETH',
                    'apellidos' => 'ARROYO MAMANI',
                    'observacion' => 'H+',
                    'padre' => null,
                    'madre' => ['dni' => '44534717', 'nombres' => 'CRISTINA', 'apellidos' => 'MAMANI RAMIREZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79367182',
                    'nombres' => 'LEONEL JULIAN',
                    'apellidos' => 'CARRERA SIMON',
                    'padre' => ['dni' => '70254927', 'nombres' => 'BASILIO SACARIAS', 'apellidos' => 'CARRERA GUTIERREZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '76740501', 'nombres' => 'BERTHA', 'apellidos' => 'SIMON RIVERA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79124202',
                    'nombres' => 'FADIA SAINETH',
                    'apellidos' => 'CARTAGENA TRINIDAD',
                    'padre' => null,
                    'madre' => ['dni' => '46055483', 'nombres' => 'LISSETE', 'apellidos' => 'TRINIDAD MATIENZO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79417271',
                    'nombres' => 'DIEGO ZAEL',
                    'apellidos' => 'GONZALEZ AGUILAR',
                    'padre' => ['dni' => '42743359', 'nombres' => 'ZAEL KAY', 'apellidos' => 'GONZALES GUTIERREZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '46001400', 'nombres' => 'ZOILA MIRIAM', 'apellidos' => 'AGUILAR PALOMINO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79329885',
                    'nombres' => 'MARGARETT LUCIANA',
                    'apellidos' => 'HUAMAN HUAMAN',
                    'padre' => null,
                    'madre' => ['dni' => '70319173', 'nombres' => 'MADELYN ANITA', 'apellidos' => 'HUAMAN AMES', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79331989',
                    'nombres' => 'MICAELA LELIA SAORY',
                    'apellidos' => 'JIMENEZ JAPA',
                    'padre' => ['dni' => '40562317', 'nombres' => 'ISAIAS', 'apellidos' => 'JIMENEZ JAUREGUI', 'sexo' => 'M'],
                    'madre' => ['dni' => '48552363', 'nombres' => 'IZABELA', 'apellidos' => 'JAPA ESPINOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79531491',
                    'nombres' => 'ALEXIA BELEN',
                    'apellidos' => 'LA TORRE GREGORIO',
                    'padre' => ['dni' => '46088412', 'nombres' => 'IVETH AGUSTINA', 'apellidos' => 'JANAMILLO CORTEZ', 'sexo' => 'F'],
                    'madre' => ['dni' => '72611485', 'nombres' => 'JOSSELIN LILIANA', 'apellidos' => 'GREGORIO INOCENTE', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79362958',
                    'nombres' => 'BRITTANY SADIE',
                    'apellidos' => 'MALPARTIDA PANDURO',
                    'padre' => ['dni' => '71220837', 'nombres' => 'FRANKLIN MICHAEL', 'apellidos' => 'MALPARTIDA RIOS', 'sexo' => 'M'],
                    'madre' => ['dni' => '70261039', 'nombres' => 'NAISHA KATIA', 'apellidos' => 'PANDURO VEGA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79258732',
                    'nombres' => 'CARLOS FRANCISCO',
                    'apellidos' => 'MONTENEGRO SCHUTZE',
                    'padre' => ['dni' => '47318472', 'nombres' => 'CARLOS JAVIER', 'apellidos' => 'MONTENEGRO LLANOS', 'sexo' => 'M'],
                    'madre' => null,
                ],
                [
                    'dni' => '79105140',
                    'nombres' => 'GIANELLA BELEN',
                    'apellidos' => 'PARDAVE JURADO',
                    'padre' => ['dni' => '80660618', 'nombres' => 'FREDY GUSTAVO', 'apellidos' => 'PARDAVE TORRES', 'sexo' => 'M'],
                    'madre' => ['dni' => '41003697', 'nombres' => 'MARIANELA', 'apellidos' => 'JURADO FLORES', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79108904',
                    'nombres' => 'JOHAN DAVID',
                    'apellidos' => 'ROSALES CAJACURI',
                    'padre' => ['dni' => '42678044', 'nombres' => 'DAVID HERMES', 'apellidos' => 'ROSALES CORONEL', 'sexo' => 'M'],
                    'madre' => ['dni' => '47603211', 'nombres' => 'YOJANI HAYDEE', 'apellidos' => 'CAJACURI SEDANO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79496760',
                    'nombres' => 'LORAINE',
                    'apellidos' => 'RUYFFELEART FRANCO',
                    'padre' => ['dni' => '09340759', 'nombres' => 'WILHELM CHRISTIAN', 'apellidos' => 'RUYFFELEART ANAYA', 'sexo' => 'M'],
                    'madre' => ['dni' => '001860235', 'nombres' => 'IRENA', 'apellidos' => 'FRANCO MONEDERO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79114499',
                    'nombres' => 'LUZ MARIA',
                    'apellidos' => 'TOLENTINO REYES',
                    'padre' => ['dni' => '04339514', 'nombres' => 'FELIX', 'apellidos' => 'TOLENTINO LOPEZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '41535899', 'nombres' => 'ELIANA NELLY', 'apellidos' => 'REYES MENDOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79281989',
                    'nombres' => 'ANGELA ROSITA',
                    'apellidos' => 'TOVAR ROJAS',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '06797676', 'nombres' => 'NILTON WILFREDO', 'apellidos' => 'TOVAR ZARATE', 'sexo' => 'M'],
                    'madre' => ['dni' => '43063571', 'nombres' => 'LILIANA MARGOT', 'apellidos' => 'ROJAS ESTRADA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79325241',
                    'nombres' => 'BRIANA VALENTINA',
                    'apellidos' => 'TRINIDAD URETA',
                    'padre' => ['dni' => '46717216', 'nombres' => 'SHOEL ELMER', 'apellidos' => 'TRINIDAD RICALDI', 'sexo' => 'M'],
                    'madre' => ['dni' => '45077069', 'nombres' => 'CINTYA IRMA', 'apellidos' => 'URETA VILLAR', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79439868',
                    'nombres' => 'BRIANNA ELIZABETH',
                    'apellidos' => 'VIDAL GUTIERREZ',
                    'padre' => null,
                    'madre' => ['dni' => '47024508', 'nombres' => 'CLAUDIA FIORELA', 'apellidos' => 'GUTIERREZ CABRERA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78793100',
                    'nombres' => 'ABIGAIL TAMARA ITAMAR',
                    'apellidos' => 'ARTEAGA MERCADO',
                    'padre' => null,
                    'madre' => ['dni' => '04303125', 'nombres' => 'MARIA ANGELICA', 'apellidos' => 'ABUELA-ESPINOZA DE MERCADO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78381117',
                    'nombres' => 'FREDO',
                    'apellidos' => 'FERNANDEZ APAZA',
                    'padre' => ['dni' => '10592098', 'nombres' => 'FRANCISCO', 'apellidos' => 'FERNANDEZ TORRES', 'sexo' => 'M'],
                    'madre' => ['dni' => '80462554', 'nombres' => 'RAYMUNDA', 'apellidos' => 'APAZA GALLEGOS', 'sexo' => 'F'],
                ],
            ],
        ],
        [
            'grado_orden' => 6,
            'seccion' => 'A',
            'estudiantes' => [
                [
                    'dni' => '79005843',
                    'nombres' => 'ITZELY GREEYS',
                    'apellidos' => 'ALVARADO ECHEVARRIA',
                    'padre' => null,
                    'madre' => ['dni' => '45927820', 'nombres' => 'GREEYS SELMY', 'apellidos' => 'ECHEVARRIA ESPINOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78540403',
                    'nombres' => 'IARITZEL KEISHA',
                    'apellidos' => 'CAJACURI PALPA',
                    'padre' => ['dni' => '70689904', 'nombres' => 'CARLOS EDUARDO', 'apellidos' => 'LUIS ROCCA', 'sexo' => 'M'],
                    'madre' => ['dni' => '43717326', 'nombres' => 'JUANA IRIS', 'apellidos' => 'PALPA VENTURA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78759777',
                    'nombres' => 'RANSSEF JOSUE',
                    'apellidos' => 'CANO ROMERO',
                    'padre' => ['dni' => '41826020', 'nombres' => 'EMILIANO WILLY', 'apellidos' => 'CANO VILLENA', 'sexo' => 'M'],
                    'madre' => ['dni' => '44700458', 'nombres' => 'HEDITH NAYDA', 'apellidos' => 'ROMERO ALHUAY', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78765279',
                    'nombres' => 'MARIANNE LUANA',
                    'apellidos' => 'CARHUA RAYMUNDO',
                    'padre' => ['dni' => '70795247', 'nombres' => 'JOSUE NEMIAS', 'apellidos' => 'CARHUA ROSALES', 'sexo' => 'M'],
                    'madre' => ['dni' => '70609122', 'nombres' => 'FRITZA PAMELA', 'apellidos' => 'RAYMUNDO SANDOVAL', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78976094',
                    'nombres' => 'ANDREA BELEN',
                    'apellidos' => 'CASTILLEJOS MOROCHO',
                    'padre' => null,
                    'madre' => ['dni' => '71088658', 'nombres' => 'MAHELY GIANINA', 'apellidos' => 'MOROCHO ZELADA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78782247',
                    'nombres' => 'YAMILA ZUMY',
                    'apellidos' => 'CHIMBIRI ORDO' . "\u{00D1}" . 'EZ',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '43365558', 'nombres' => 'JUAN MOISES', 'apellidos' => 'CHIMBIRI PAREJA', 'sexo' => 'M'],
                    'madre' => ['dni' => '44667333', 'nombres' => 'EVELYN DEISY', 'apellidos' => 'ORDO' . "\u{00D1}" . 'EZ ESPINOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '81249050',
                    'nombres' => 'ANDREA LIZETH',
                    'apellidos' => 'CORDERO CHAMORRO',
                    'padre' => ['dni' => '70255127', 'nombres' => 'JOSE LEONARDO', 'apellidos' => 'CORDERO CHAMORRO', 'sexo' => 'M'],
                    'madre' => null,
                ],
                [
                    'dni' => '78928865',
                    'nombres' => 'DHIAGO DDAJUAN',
                    'apellidos' => 'DE LA CRUZ DIAZ',
                    'padre' => null,
                    'madre' => ['dni' => '45688555', 'nombres' => 'ALINA MARIELA', 'apellidos' => 'DIAZ HUACCHA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79027101',
                    'nombres' => 'JHOMARA LELIS',
                    'apellidos' => 'FLORES POMACHAGUA',
                    'padre' => null,
                    'madre' => ['dni' => '44218013', 'nombres' => 'JHOANNA MAYRA', 'apellidos' => 'POMACHAGUA KOELL', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78840918',
                    'nombres' => 'THERRY THIAGO',
                    'apellidos' => 'GUEVARA AYALA',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '80132521', 'nombres' => 'MANUEL', 'apellidos' => 'GUEVARA AMEZAGA', 'sexo' => 'M'],
                    'madre' => ['dni' => '45609432', 'nombres' => 'KATHERINE XENIA', 'apellidos' => 'AYALA GARCIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78998815',
                    'nombres' => 'ARIADNE ALLISON',
                    'apellidos' => 'INGA PEINADO',
                    'padre' => ['dni' => '19969868', 'nombres' => 'ARNALDO', 'apellidos' => 'INGA RAMIREZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '19969969', 'nombres' => 'LOURDES', 'apellidos' => 'PEINADO SANTANA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78612220',
                    'nombres' => 'ALESSANDRO DHARIEL',
                    'apellidos' => 'JIMENEZ CESPEDES',
                    'padre' => ['dni' => '48154296', 'nombres' => 'MARTIN HAROLD', 'apellidos' => 'JIMENEZ BOZA', 'sexo' => 'M'],
                    'madre' => ['dni' => '77227231', 'nombres' => 'ERIKA GERALDINE', 'apellidos' => 'CESPEDES ACOSTA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78728519',
                    'nombres' => 'MAYLI FERNANDA',
                    'apellidos' => 'JUAREZ ARIAS',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '41227940', 'nombres' => 'MARCO AURELIO', 'apellidos' => 'JUAREZ PARRAGA', 'sexo' => 'M'],
                    'madre' => ['dni' => '43706608', 'nombres' => 'LIZZETH KATERINE', 'apellidos' => 'ARIAS YALICO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78913237',
                    'nombres' => 'JADNIEL BRIAN',
                    'apellidos' => 'MALPARTIDA ROCHA',
                    'padre' => ['dni' => '44362357', 'nombres' => 'GUILLERMO', 'apellidos' => 'MALPARTIDA ALVAREZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '46654157', 'nombres' => 'ROSA YOVANA', 'apellidos' => 'ROCHA ASCANOA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78856909',
                    'nombres' => 'IAN DITHER',
                    'apellidos' => 'MICHE SALVATIERRA',
                    'observacion' => 'H+',
                    'padre' => null,
                    'madre' => ['dni' => '48669322', 'nombres' => 'KATTY ELIZABETH', 'apellidos' => 'SALVATIERRA JACINTO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79028895',
                    'nombres' => 'JEICK MATEO',
                    'apellidos' => 'MIRANDA ARIZOLA',
                    'padre' => null,
                    'madre' => ['dni' => '47013997', 'nombres' => 'ANDREINA', 'apellidos' => 'ARIZONA NAZAR', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78845350',
                    'nombres' => 'KRISTHALY GISELL',
                    'apellidos' => 'MIRANDA SALAS',
                    'padre' => ['dni' => '43302462', 'nombres' => 'ELVIS ERICK', 'apellidos' => 'MIRANDA DIAZ', 'sexo' => 'M'],
                    'madre' => ['dni' => '44084339', 'nombres' => 'GISELLA MELIZA', 'apellidos' => 'SALAS ESPINOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78532536',
                    'nombres' => 'VALESHKA MERCEDES',
                    'apellidos' => 'OSCATEGUI RAYMONDI',
                    'padre' => ['dni' => '20072736', 'nombres' => 'JORGE LUIS', 'apellidos' => 'OSCATEGUI TORRES', 'sexo' => 'M'],
                    'madre' => ['dni' => '41165545', 'nombres' => 'JESENIA MERCEDES', 'apellidos' => 'RAYMONDI ALANIA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78879399',
                    'nombres' => 'ZHOE KRISTEL ALESSIA',
                    'apellidos' => 'POMAZONGO SUAREZ',
                    'padre' => null,
                    'madre' => ['dni' => '71117534', 'nombres' => 'ANA MARIA', 'apellidos' => 'SUAREZ SANTOS', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78819628',
                    'nombres' => 'RODRIGO ALEJANDRO',
                    'apellidos' => 'RAMOS APAZA',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '04338353', 'nombres' => 'WALTER RUDY', 'apellidos' => 'RAMOS TRUJILLO', 'sexo' => 'M'],
                    'madre' => ['dni' => '41674150', 'nombres' => 'VANESSA GRIPS', 'apellidos' => 'APAZA MEDRANO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79030221',
                    'nombres' => 'JOSE FABIO',
                    'apellidos' => 'ROCA SALVATIERRA',
                    'padre' => null,
                    'madre' => ['dni' => '44467005', 'nombres' => 'JANETH RENE', 'apellidos' => 'SALVATIERRA JACINTO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '80897106',
                    'nombres' => 'YEDELEE MILAGROS',
                    'apellidos' => 'ROMAN ORTIZ',
                    'padre' => null,
                    'madre' => ['dni' => '72407087', 'nombres' => 'GLEIDY', 'apellidos' => 'ORTIZ FRAY', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78957860',
                    'nombres' => 'VALENTINA INES',
                    'apellidos' => 'VARGAS TRAVI',
                    'padre' => ['dni' => '41268744', 'nombres' => 'EDGAR EDWIN', 'apellidos' => 'VARGAS CACERES', 'sexo' => 'M'],
                    'madre' => ['dni' => '43066212', 'nombres' => 'ISABEL', 'apellidos' => 'TRAVI ASTOHUAMAN', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78721537',
                    'nombres' => 'BENJAMIN IVAN',
                    'apellidos' => 'ZENTENO URRUTIA',
                    'padre' => ['dni' => '42653175', 'nombres' => 'JOSE LIONEL', 'apellidos' => 'ZENTENO RAMOS', 'sexo' => 'M'],
                    'madre' => ['dni' => '43293642', 'nombres' => 'ANNELICE FRANCESCA', 'apellidos' => 'URRUTIA BEJARANO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78504684',
                    'nombres' => 'LUCIANA',
                    'apellidos' => 'PACHECO GUERRA',
                    'padre' => ['dni' => '42609218', 'nombres' => 'MARTIN', 'apellidos' => 'PACHECO PAJUELO', 'sexo' => 'M'],
                    'madre' => null,
                ],
                [
                    'dni' => '81455458',
                    'nombres' => 'CAMILA DANIELA',
                    'apellidos' => 'CANALES CASTANEDA',
                    'observacion' => 'H+',
                    'padre' => ['dni' => '42084301', 'nombres' => 'JACK POLH', 'apellidos' => 'CANALES SOLIS', 'sexo' => 'M'],
                    'madre' => ['dni' => '41424137', 'nombres' => 'ENMA', 'apellidos' => 'CASTANEDA OYARCE', 'sexo' => 'F'],
                ],
                [
                    'dni' => '81589763',
                    'nombres' => 'DAYANA',
                    'apellidos' => 'MORALES VALDERRAMA',
                    'padre' => ['dni' => '40448729', 'nombres' => 'CARLOS OSCAR', 'apellidos' => 'MORALES TOVAL', 'sexo' => 'M'],
                    'madre' => ['dni' => '40376222', 'nombres' => 'KETTY', 'apellidos' => 'VALDERRAMA MENDOZA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78943347',
                    'nombres' => 'ASHLEY',
                    'apellidos' => 'MALLQUI TAMAYO',
                    'padre' => ['dni' => '41791957', 'nombres' => 'HECTOR', 'apellidos' => 'MALLQUI CHACON', 'sexo' => 'M'],
                    'madre' => ['dni' => '40698216', 'nombres' => 'IDA ROSA', 'apellidos' => 'TAMAYO SAMANIEGO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78753828',
                    'nombres' => 'GENESIS NOELIA',
                    'apellidos' => 'YAURI MAMANI',
                    'padre' => null,
                    'madre' => ['dni' => '41489081', 'nombres' => 'SONIA ERIKA', 'apellidos' => 'MAMANI COAQUIRA', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78940707',
                    'nombres' => 'PATRICK',
                    'apellidos' => 'LUNA HUAMAN',
                    'padre' => ['dni' => '80216188', 'nombres' => 'LUIS ALBERTO', 'apellidos' => 'LUNA HUARANGA', 'sexo' => 'M'],
                    'madre' => ['dni' => '43437983', 'nombres' => 'ELSA', 'apellidos' => 'HUAMAN AQUINO', 'sexo' => 'F'],
                ],
                [
                    'dni' => '78763620',
                    'nombres' => 'KETHLY ARIANA',
                    'apellidos' => 'LLALLAHUI HIDALGO',
                    'observacion' => 'H+',
                    'padre' => null,
                    'madre' => ['dni' => '48187931', 'nombres' => 'SUSAN', 'apellidos' => 'HIDALGO ORTIZ', 'sexo' => 'F'],
                ],
                [
                    'dni' => '79091059',
                    'nombres' => 'ALONDRA CRISS',
                    'apellidos' => 'CARPENA CAPCHA',
                    'padre' => null,
                    'madre' => ['dni' => '48806671', 'nombres' => 'BETTY GABRIELA', 'apellidos' => 'CAPCHA OROSCO', 'sexo' => 'F'],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar Primaria.');
            return;
        }

        $this->asegurarPeriodosAcademicos($anio);

        $periodo = PeriodoAcademico::actualParaAnio($anio->id)
            ?? PeriodoAcademico::where('anio_escolar_id', $anio->id)->orderBy('orden')->first();

        if (! $periodo) {
            $this->command?->warn('No existe un periodo academico para el anio escolar activo.');
            return;
        }

        foreach (self::GRUPOS as $grupo) {
            $grado = Grado::where('nivel', 'Primaria')
                ->where('orden', $grupo['grado_orden'])
                ->first();

            if (! $grado) {
                $this->command?->warn("No se encontro el grado {$grupo['grado_orden']} de Primaria.");
                continue;
            }

            $seccion = Seccion::firstOrCreate(
                [
                    'anio_escolar_id' => $anio->id,
                    'grado_id' => $grado->id,
                    'nombre' => $grupo['seccion'],
                ],
                [
                    'turno' => 'Manana',
                ]
            );

            foreach ($grupo['estudiantes'] as $estudianteData) {
                DB::transaction(function () use ($estudianteData, $anio, $periodo, $grado, $seccion) {
                    $this->registrarEstudiante($estudianteData, $anio->id, $periodo->id, $grado->id, $seccion->id);
                });
            }
        }

        $this->command?->info('Primaria registrada con estudiantes y apoderados.');
    }

    private function asegurarPeriodosAcademicos(AnioEscolar $anio): void
    {
        if (PeriodoAcademico::where('anio_escolar_id', $anio->id)->exists()) {
            return;
        }

        $anioCalendario = (int) preg_replace('/\D+/', '', (string) $anio->nombre);
        if ($anioCalendario < 2000) {
            $anioCalendario = (int) date('Y');
        }

        $ahora = now();
        $codigoBase = trim((string) $anio->nombre) !== '' ? trim((string) $anio->nombre) : (string) $anioCalendario;

        PeriodoAcademico::insert([
            [
                'anio_escolar_id' => $anio->id,
                'codigo' => $codigoBase . '-0',
                'nombre' => 'Vacacional',
                'fecha_inicio' => "{$anioCalendario}-01-01",
                'fecha_fin' => "{$anioCalendario}-02-28",
                'es_vacacional' => true,
                'orden' => 0,
                'activo' => false,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'anio_escolar_id' => $anio->id,
                'codigo' => $codigoBase . '-1',
                'nombre' => 'Periodo 1',
                'fecha_inicio' => "{$anioCalendario}-03-01",
                'fecha_fin' => "{$anioCalendario}-07-31",
                'es_vacacional' => false,
                'orden' => 1,
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'anio_escolar_id' => $anio->id,
                'codigo' => $codigoBase . '-2',
                'nombre' => 'Periodo 2',
                'fecha_inicio' => "{$anioCalendario}-08-01",
                'fecha_fin' => "{$anioCalendario}-12-31",
                'es_vacacional' => false,
                'orden' => 2,
                'activo' => false,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ]);
    }

    private function registrarEstudiante(
        array $estudianteData,
        int $anioEscolarId,
        int $periodoAcademicoId,
        int $gradoId,
        int $seccionId
    ): void {
        $dni = $this->normalizarDni($estudianteData['dni']);
        if (! $dni) {
            return;
        }

        $nombreCompleto = trim($estudianteData['apellidos'] . ' ' . $estudianteData['nombres']);

        $user = User::firstOrCreate(
            ['dni' => $dni],
            [
                'name' => $nombreCompleto,
                'email' => $this->emailEstudiante($dni),
                'password' => Hash::make($dni),
            ]
        );

        $userUpdates = [];
        if ($user->name !== $nombreCompleto) {
            $userUpdates['name'] = $nombreCompleto;
        }
        if (trim((string) $user->email) === '') {
            $userUpdates['email'] = $this->emailEstudiante($dni);
        }
        if (! empty($userUpdates)) {
            $user->update($userUpdates);
        }

        if (! $user->hasRole('Estudiante')) {
            $user->assignRole('Estudiante');
        }

        $estudiante = Estudiante::updateOrCreate(
            ['dni' => $dni],
            [
                'user_id' => $user->id,
                'codigo' => $dni,
                'nombres' => $estudianteData['nombres'],
                'apellidos' => $estudianteData['apellidos'],
                'estado' => 'activo',
            ]
        );

        if ((int) ($estudiante->user_id ?? 0) !== $user->id) {
            $estudiante->user_id = $user->id;
            $estudiante->save();
        }

        $padre = $this->registrarApoderado($estudianteData['padre'] ?? null);
        $madre = $this->registrarApoderado($estudianteData['madre'] ?? null);

        $this->sincronizarApoderados($estudiante, $padre, $madre);

        Matricula::updateOrCreate(
            [
                'anio_escolar_id' => $anioEscolarId,
                'periodo_academico_id' => $periodoAcademicoId,
                'estudiante_id' => $estudiante->id,
            ],
            [
                'grado_id' => $gradoId,
                'seccion_id' => $seccionId,
                'fecha_matricula' => now()->toDateString(),
                'estado' => $estudianteData['estado_matricula'] ?? Matricula::ESTADO_ACTIVA,
                'fecha_retiro' => ($estudianteData['estado_matricula'] ?? null) === Matricula::ESTADO_RETIRADA
                    ? now()->toDateString()
                    : null,
                'repitente' => false,
                'observacion' => $estudianteData['observacion'] ?? null,
            ]
        );
    }

    private function registrarApoderado(?array $apoderadoData): ?Apoderado
    {
        if (! $apoderadoData) {
            return null;
        }

        $dni = $this->normalizarDni($apoderadoData['dni'] ?? null);
        $nombres = trim((string) ($apoderadoData['nombres'] ?? ''));
        $apellidos = trim((string) ($apoderadoData['apellidos'] ?? ''));

        if ($nombres === '' || $apellidos === '') {
            return null;
        }

        $identificador = $dni
            ? ['dni' => $dni]
            : ['dni' => null, 'nombres' => $nombres, 'apellidos' => $apellidos];

        return Apoderado::updateOrCreate(
            $identificador,
            [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'dni' => $dni,
                'sexo' => $apoderadoData['sexo'] ?? null,
            ]
        );
    }

    private function sincronizarApoderados(Estudiante $estudiante, ?Apoderado $padre, ?Apoderado $madre): void
    {
        $syncData = [];
        $principalId = null;

        if ($padre) {
            $principalId = $padre->id;
            $syncData[$padre->id] = [
                'relacion' => 'Padre',
                'principal' => true,
            ];
        }

        if ($madre) {
            if (! $principalId) {
                $principalId = $madre->id;
            }

            $syncData[$madre->id] = [
                'relacion' => 'Madre',
                'principal' => $madre->id === $principalId,
            ];
        }

        if (empty($syncData)) {
            return;
        }

        $estudiante->apoderados()->sync($syncData, false);

        DB::table('apoderado_estudiante')
            ->where('estudiante_id', $estudiante->id)
            ->update(['principal' => false]);

        if ($principalId) {
            DB::table('apoderado_estudiante')
                ->where('estudiante_id', $estudiante->id)
                ->where('apoderado_id', $principalId)
                ->update(['principal' => true]);
        }
    }

    private function emailEstudiante(string $dni): string
    {
        return $dni . '@reyschool.edu.pe';
    }

    private function normalizarDni(?string $dni): ?string
    {
        $normalizado = preg_replace('/\D+/', '', trim((string) $dni)) ?? '';

        return $normalizado !== '' ? $normalizado : null;
    }
}
