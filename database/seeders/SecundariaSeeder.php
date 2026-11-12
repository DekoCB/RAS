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

class SecundariaSeeder extends Seeder
{
    private const SECCION = 'A';

    private const GRUPOS = [
        [
            'grado_orden' => 1,
            'estudiantes' => [
                ['78430711', 'CAPILLO MARTINEZ', 'JOSHUA SEBASTIAN', ['73570532', 'CAPILLO TORRES', 'EDWARD HUMBERTO', 'M'], ['75669068', 'MARTINEZ MUCHA', 'STEPHANY MELANIE', 'F']],
                ['80849658', 'ECHEANDIA HUAMAN', 'JIM SUSANNA', ['42208385', 'ECHEANDIA OJEDA', 'SAMUEL', 'M'], ['46727679', 'HUAMAN ALANIA', 'NORMA', 'F']],
                ['78454700', 'ESPINOZA ZELAYA', 'HEDELAY YENIVIS', null, ['61561912', 'ZELAYA ORTIZ', 'MIRIAM MIRELLA', 'F']],
                ['78072397', 'INCHE TERRY', 'THIAGO BRANDON', ['04001786', 'INCHE CONDOR', 'RODOLFO MARINO', 'M'], ['43980612', 'TERRY TEMPLE', 'ESTELA IRMA', 'F']],
                ['80849679', 'LEGUIA DURAN', 'JETHRO KLAUS', ['42053569', 'LEGUIA ALDERETE', 'SERGIO', 'M'], ['44994841', 'DURAN ZEVALLOS', 'QUEREN', 'F']],
                ['78173159', 'MARCELO PANDURO', 'TIZIANA YADELI', null, ['48662240', 'PANDURO ESPINOZA', 'NASHARI', 'F']],
                ['78187412', 'MAURTUA ROMAN', 'GUSTAVO MATEO', ['04341230', 'MAURTUA AGON', 'RAUL ANIBAL', 'M'], ['04340759', 'ROMAN VELA', 'FLOR CARMEN', 'F']],
                ['80849697', 'OROZCO CASTRO', 'BELEN ALEXANDRA', null, ['44427968', 'CASTRO CARHUACHUCO', 'VERONICA SUSAN', 'F']],
                ['78179274', 'ROJAS ALARCON', 'LUCIANA MICAELA', ['09446937', 'ROJAS RUMRILL', 'JUAN HUMBERTO', 'M'], ['42690065', 'ALARCON PORTILLO', 'ELIZABETH', 'F']],
                ['78536169', 'SALAS DEL ALCAZAR', 'RODRIGO', ['04307440', 'SALAS SOLANO', 'GILMAR', 'M'], ['40088161', 'DEL ALCAZAR GARCIA', 'MONICA ISABEL', 'F']],
                ['78457336', 'TOLENTINO CANO', 'EMANUEL FERNANDO', ['44547977', 'TOLENTINO MALMA', 'RODIN', 'M'], ['71408833', 'CANO CALERO', 'HILDA ROCIO', 'F']],
                ['81394049', 'VASQUEZ BERROSPI', 'HANNSEL MIGUEL JESUS', ['4956580', 'VASQUEZ ORTIZ', 'ALAIN GASTULO', 'M'], ['76961062', 'BERROSPI VEGA', 'ROSA LUZ', 'F']],
                ['78218437', 'VENTO AMPUERO', 'JOAQUIN LEONARDO', ['04342070', 'VENTO ESTRELLA', 'DANIEL', 'M'], ['10432186', 'AMPUERO CHAVEZ', 'LUCIA', 'F']],
                ['81551069', 'YUPARI BALLESTEROS', 'JORDI ALEJANDRO', ['10808612', 'YUPARI CAPDEVILA', 'VICTOR JONATHAN', 'M'], ['47837142', 'TUDELA THEO', 'ANETTE MILAGROS', 'F']],
                ['79283389', 'BONILLA HUANCAHUARI', 'LUCIANA', ['48192553', 'BONILLA ASTO', 'MARCO ANTONIO', 'M'], ['48881883', 'HUANCAHUARI ESPINOZA', 'NOZA DIANA', 'F']],
                ['81140115', 'PACO VALVERDE', 'ADRIANO MIAEL', ['41969931', 'PACO RODRIGUEZ', 'RODRIGO YHOEL', 'M'], ['46129656', 'VALVERDE GONZALES', 'MARTHA ISABEL', 'F']],
                ['77859581', 'DE LA CRUZ CHINCHAY', 'SAYURI YAMILE', ['42395595', 'DE LA CRUZ MALPARTIDA', 'EDWIN NOE', 'M'], null],
                ['78208953', 'ALEGRIA VALLE', 'IYEHIL XIOMARA', ['44241661', 'ZEVALLOS ORTIZ', 'EDWIN RAUL', 'M'], ['78104514', 'VALLE VERASTEGUI', 'CELSA PAOLA', 'F']],
                ['78486964', 'PARDO ACOSTA', 'CAMILA IZETH', ['47147569', 'PARDO HUARANGA', 'JEAN CARLOS', 'M'], ['76547854', 'ACOSTA QUISPE', 'ESTEFANY LIZ', 'F']],
                ['80850418', 'GOMEZ CALERO', 'DYLAND YANDEL', ['45271554', 'GOMEZ VIZURRAGA', 'WILLIAM FIDEL', 'M'], ['46235923', 'CALERO MONAGO', 'JESICA JANETH', 'F']],
                ['78324605', 'GUTIERREZ ALEGRE', 'BRYANNA VALENTINA', ['40860568', 'GUTIERREZ VILLA', 'PERCY RAUL', 'M'], ['45533938', 'ALEGRE CACERES', 'ROCIO PILAR', 'F']],
                ['81413818', 'ROJAS OREJON', 'IREH USHERET', ['70229086', 'ROJAS PARIONA', 'ANTONY YOSEL', 'M'], ['70132377', 'OREJON LOPE', 'KELLY MARITZA', 'F']],
                ['78172277', 'LUNA FARFAN', 'YHAYESHA', [null, 'LUNA DIAZ', 'RICARDO ANTONIO', 'M'], ['41768089', 'FARFAN VERDE', 'ANGELA', 'F']],
                ['78283787', 'EFOS ZARATE', 'JONATHAN MATIAS', ['10882755', 'EFOS PEREZ', 'LUIS ALBERTO', 'M'], ['44263961', 'ZARATE PEREZ', 'ELSA ADELINDA', 'F']],
                ['78353289', 'CASTRO BARBOZA', 'ANGEL LUCAS', ['27741781', 'ABUELO-CRUZ CHAVEZ', 'FRANCISCO', 'M'], null],
            ],
        ],
        [
            'grado_orden' => 2,
            'estudiantes' => [
                ['77718723', 'AGUERO BERROSPI', 'MATHIAS RAUL', null, ['04307365', 'BERROSPI YALICO', 'NORMA', 'F']],
                ['77789637', 'ALANIA YURIVILCA', 'JARET FERNANDO', null, ['71268784', 'YURIVILCA PUENTE', 'SHEYLA MAGGALI', 'F']],
                ['77761014', 'ALVAREZ ROSADO', 'ANASOL', [null, 'ALVAREZ CEDRON', 'JHON VICTOR', 'M'], ['46574847', 'ROSADO CELEDONIO', 'EVELYN BETZAYDA', 'F']],
                ['80958701', 'BARRA DAMANO', 'PIA KIARA', ['70304327', 'CASTANEDA RIOS', 'JUAN CHRISTIAN', 'M'], [null, 'DAMIANO ROLDAN', 'KATHERINE ARACELI', 'F']],
                ['77844686', 'CABRERA VERDE', 'ADRIANE ROSARIO', null, ['46424541', 'VERDE FASIL', 'CARMEN MARIA', 'F']],
                ['77874121', 'DURAND RODRIGUEZ', 'XIOMARA ARELY', ['10665140', 'DURAND AGUILAR', 'DAVID CARLOS', 'M'], ['44501092', 'RODRIGUEZ CONTRERAS', 'ARACELI EUSTASIA', 'F']],
                ['63140788', 'FIGUEROA PAUCAR', 'FABIANNA', ['45542999', 'FIGUEROA SINCHE', 'JESUS MARTIN', 'M'], ['43696773', 'PAUCAR RAMON', 'BLANCA TATIANA', 'F']],
                ['80745601', 'LA TORRE TARAZONA', 'YUSELY ISABEL', ['04307155', 'LA TORRE HASSINGER', 'ELIZAR DAVID', 'M'], ['48380035', 'TARAZONA CORSO', 'YULIANA', 'F']],
                ['80958703', 'MACHACCA GARCIA', 'ALISON LUCERO', ['41995662', 'MACHACCA NAHUIRIMA', 'EDMUNDO', 'M'], ['40542337', 'GARCIA SALAZAR', 'FLOR MARIA', 'F']],
                ['63251889', 'MIRANDA ZEVALLOS', 'DAYLIN DAYANA', null, ['77437781', 'ZEVALLOS RAMOS', 'MAYUMI', 'F']],
                ['77942127', 'ORE MENESES', 'HYECK WILLIAM', ['44413170', 'AEDO VILLAGARAY', 'DANTE ALEXANDER', 'M'], ['46949384', 'MENESES VILLANUEVA', 'MARIZA FLORIZA', 'F']],
                ['77882910', 'OROSCO UGARTE', 'JHOF AXEL', ['04339134', 'OROSCO MORALES', 'LUIS ALFREDO', 'M'], ['45925607', 'UGARTE TRUJILLO', 'DINA ELENA', 'F']],
                ['78003303', 'PUENTE AGUILAR', 'PRIYA LY', ['70288311', 'PUENTE HUARANGA', 'HODI KIARS', 'M'], ['70784575', 'AGUILAR BARRA', 'FIORELLA OLINDA', 'F']],
                ['63689744', 'RAMIREZ HUMBER', 'JHORDYN VICTOR', null, ['31932790', 'CRISTOBAL DE REA', 'DELFINA', 'F']],
                ['77950187', 'ROJAS GUERRA', 'CHRISTOFER ANTHONY', ['04061973', 'ABUELO-ARREDONDO ALVARADO', 'ALEX', 'M'], ['04074282', 'RICALDE PANDURO', 'JUANA PAULINA', 'F']],
                ['80849835', 'ROJAS JIMENEZ', 'MANUEL ENRIQUE', ['41117786', 'ROJAS ORIZANO', 'ENRIQUE', 'M'], ['09501163', 'JIMENEZ VARGAS', 'TEOFILA', 'F']],
                ['81110518', 'ROMAN MENDOZA', 'EIMY VALENTINA', ['41284989', 'ROMAN VELA', 'ANIBAL ENRIQUE', 'M'], ['40259790', 'MENDOZA ARIAS', 'JACQUELENI', 'F']],
                ['77933201', 'SALAZAR REQUEJO', 'KEVIN MJAEL', ['40026572', 'SALAZAR AMES', 'KEVIN VIRGILIO', 'M'], ['44546209', 'REQUEJO MEJIA', 'MIRIAN ROCIO', 'F']],
                ['63077751', 'ZENTENO URRUTIA', 'IGNACIO JOSE', ['42653175', 'ZENTENO RAMOS', 'JOSE LIONEL', 'M'], ['43293642', 'URRUTIA BEJARANO', 'ANNELICE FRANCESCA', 'F']],
                ['77941272', 'ZEVALLOS VERDE', 'LYONEL EDWIN', ['44241661', 'ZEVALLOS ORTIZ', 'RAUL EDWIN', 'M'], ['76828445', 'VERDE ESPINOZA', 'LIDIA', 'F']],
                ['63233995', 'OSPINA MACETAS', 'VAYOLET ANGELA', ['43171537', 'OSPINA MAYTA', 'IVAN RAUL', 'M'], ['46719011', 'MACETAS HINOJOSA', 'ADA INES', 'F']],
                ['80849895', 'ORDONEZ OROSCO', 'JUNIOR FABRICIO', null, ['44040512', 'OROSCO PUENTE', 'YOVANCA NEVENCA', 'F']],
                ['63776512', 'RUIZ ROJAS', 'DABBEY VIDER', ['46376642', 'RUIZ MENDOZA', 'DEIBY VIDER', 'M'], ['73432195', 'ROJAS VASQUEZ', 'IRIS', 'F']],
                ['80850202', 'MALLQUI VARGAS', 'HILARY AKEMY', ['10674557', 'MALLQUI PAREDES', 'JEREMIAS DANIEL', 'M'], ['24487767', 'VARGAS FUENTES', 'SONIA', 'F']],
                ['63793993', 'TELLO VALERO', 'JOSUE CALEB', ['41416108', 'TELLO ESPINOZA', 'ELVIS', 'M'], ['44017657', 'VALERO BARJA DE TELLO', 'ELSA', 'F']],
                ['77759939', 'PANIAGUA LAURENTE', 'LUZ', ['47259730', 'PANIAGUA ALVARADO', 'JHON LENIN', 'M'], ['45281139', 'LAURENTE LANAZCA', 'LUZ SANDRA', 'F']],
                ['90415262', 'SILLO PINAS', 'ZEUS THIAGO', ['42491674', 'SILLO JIMENEZ', 'ELVIS FERMIN', 'M'], null],
            ],
        ],
        [
            'grado_orden' => 3,
            'estudiantes' => [
                ['63069151', 'CANCHARI TICA', 'SAYUMI FERNANDA', ['04330571', 'CANCHARI PALOMINO', 'JOSE', 'M'], ['40440523', 'TICA MACHACA', 'LITA MARIVEL', 'F']],
                ['77558903', 'CANO FERNANDEZ', 'ANCELES', ['42675153', 'CANO OSORIO', 'LITMAN', 'M'], ['44112241', 'FERNANDEZ UBALDO', 'IDA PILAR', 'F']],
                ['62952203', 'CARHUACHUCO HUARANGA', 'KETTY ESTELINA', ['4303606', 'CARHUACHUCO PENA', 'ARMANDO VICENTE', 'M'], null],
                ['63069234', 'DURAND ABREGU', 'HANTUAN SAUL', ['70782132', 'DURAND PALOMINO', 'GILMER DANTE', 'M'], null],
                ['77453334', 'GALINDO HIDALGO', 'FERGIE LEILY', ['46134705', 'GALINDO SOTELO', 'JOAQUIN', 'M'], ['46753221', 'HIDALGO ORTIZ', 'LEILY', 'F']],
                ['63323514', 'GARCIA CAMPOS', 'DIEGO ALONSO', ['44295644', 'GARCIA HERMOZA', 'JORGE LUIS', 'M'], ['61474263', 'CAMPOS VILLA ZENIA', 'FIORELLA', 'F']],
                ['77410549', 'GOMEZ DE LA CRUZ', 'AURORA GUADALUPE', null, ['43729501', 'DE LA CRUZ RIVAS', 'MARISOL ADITH', 'F']],
                ['63689749', 'INOCENTE DURAND', 'LUCIANA JENIFER', null, ['960893967', 'INOCENTE ESPINOZA', 'IRENEO ODON', 'M']],
                ['63140757', 'LA TORRE GREGORIO', 'KAORI NATZUMI', ['46088412', 'JARAMILLO CORTEZ', 'IVETH AGUSTINA', 'F'], ['72611485', 'GREGORIO INOCENTE', 'JOSELIN LILIANA', 'F']],
                ['62952184', 'MAPELLI ROMERO', 'EMMANUEL DERIK', null, ['04338673', 'ROMERO SCHRADER', 'ROMY BETSABETH', 'F']],
                ['62822701', 'VOGT GARCIA', 'LYA ALLICE', ['41712537', 'VOGT CHURRANGO', 'RYNNER ANTHONY', 'M'], ['42801053', 'GARCIA ABREGU', 'STEFANIE YENNIFER', 'F']],
                ['63069134', 'ZEVALLOS CASIMIR', 'BENIGNO ISAI MIGUEL', ['04077639', 'ZEVALLOS CASTRO', 'MIGUEL JONAS', 'M'], ['04077644', 'CASIMIR CARLIER', 'OLINDA', 'F']],
                ['62634389', 'BENITO SANCHEZ', 'ESTHEFAN DANNIEL', ['45948678', 'BENITO GOMEZ', 'ALEJANDRO RAUL', 'M'], ['47826801', 'SANCHEZ ESPEJO', 'LEYDI LUZ', 'F']],
                ['61866407', 'SALAS ROMERO', 'MARCK SANDERS', ['04338477', 'SALAS CAPIZO', 'JORGE', 'M'], ['46934945', 'ROMERO ALHUAY', 'LILIANA', 'F']],
                ['77584287', 'GAMARRA CARRERA', 'VALERIA ALESSANDRA', ['9843613', 'GAMARRA GONZALES', 'BRUNO HALMAR', 'M'], ['41522416', 'CARRERA HUAMAN', 'ANA LIDIA', 'F']],
                ['62829368', 'PACO VALVERDE', 'FABRIZIO SEBASTIAN', ['41969931', 'PACO RODRIGUEZ', 'RODRIGO YHOEL', 'M'], ['46129656', 'VALVERDE GONZALES', 'MARTHA ISABEL', 'F']],
                ['63080909', 'ALEGRIA ALVA', 'LARIZA', ['10606920', 'ALEGRIA CHAHUA', 'BORIS CARLOF', 'M'], ['44161619', 'ALVA BAZAN', 'ROSA LILIANA', 'F']],
                ['63283469', 'CRIJALVA DE LA CRUZ', 'YURLEY MARICIELO', null, ['47269655', 'DE LA CRUZ SANTOS', 'ROXANA BEATRIZ', 'F']],
                ['62777046', 'CORTIJO BELTRAN', 'FERNANDA', ['71270610', 'CORTIJO VEGA', 'WILMER WILFREDO', 'M'], ['71206136', 'BELTRAN WERLEN', 'JAKELYN EVELYN', 'F']],
                ['62863798', 'VILLA VALLADARES', 'ZULET NICOLE', ['49590223', 'VILLA CRISOSTOMO', 'JESUS', 'M'], ['43564829', 'VALLADARES HUARCAYA', 'GUEYSI', 'F']],
                ['77003878', 'ZAPATA BESARES', 'SEBASTIAN PIERO', ['41415569', 'ZAPATA ESTACA', 'ESTEBAN BONIFOS', 'M'], ['46711946', 'BASARES RODRIGUEZ', 'CENAIDA', 'F']],
            ],
        ],
        [
            'grado_orden' => 4,
            'estudiantes' => [
                ['62634467', 'ALVAREZ THEO', 'ALVARO', ['71326456', 'HERMANA-ALVAREZ THEO', 'BRITNEY', 'F'], ['43410762', 'THEO ESPINOZA', 'AGNNE BEATRIZ', 'F']],
                ['62634422', 'CANO FABIAN', 'BRAD JOEL', null, ['40661535', 'FABIAN VEGA', 'ANA MARIA', 'F']],
                ['62634269', 'CHIRINOS DE LA CUBA', 'DUANNA ARLETTE', ['16688754', 'CHIRINOS CASTRO', 'JORGE ENRIQUE', 'M'], ['42826005', 'DE LA CUBA QUISPE', 'CAROL ARLETTE', 'F']],
                ['76492624', 'CUBA ROSALES', 'MARCOS JAIRO', [null, 'CUBA GUTIERREZ', 'VICTOR', 'M'], ['42393684', 'ROSALES CALUPE', 'MARCIA MAGALLI', 'F']],
                ['62634461', 'GONZALES PEREZ', 'LESLY', null, ['22308969', 'PEREZ FLORES', 'SUSANA', 'F']],
                ['62634495', 'HUAMAN TEMBLADERA', 'CHRISTOPHER THIAGO', ['41117784', 'HUAMAN LOYA', 'EDWARD TIMOTEO', 'M'], ['46915882', 'TEMBLADERA ASTUPINAN', 'ZESY LAURA', 'F']],
                ['62315930', 'HUARACA GSTIR', 'DYLAN RAUL', ['42216946', 'HUARACA ALARCON', 'VICTOR RAUL', 'M'], ['45300183', 'GSTIR CALDERON', 'CELINA', 'F']],
                ['62473075', 'INCHE TERRY', 'JENNIFER GRACE', ['04001786', 'INCHE CONDOR', 'RODOLFO MARINO', 'M'], ['43980612', 'TERRY TEMPLE', 'ESTELA IRMA', 'F']],
                ['62634258', 'INOCENTE ATOCHE', 'LEYDI OREANA', ['20106434', 'INOCENTE CAMPOS', 'TEODORO ALEJANDRO', 'M'], ['41106512', 'ATOCHE ESPINOZA', 'LILIANA ELLUZ', 'F']],
                ['62825073', 'JORGE BENDEZU', 'ABIGAIL BELEN', ['20078999', 'JORGE FLORES', 'EDUARDO', 'M'], ['20078357', 'BENDEZU SARMIENTO', 'DORGAS', 'F']],
                ['62646407', 'MACETAS SANCHEZ', 'EDUARDO DAVID', null, ['43498442', 'SANCHEZ ESPEJO', 'SHARON VANESSA', 'F']],
                ['008918138', 'PEREIRA ROMERO', 'ANYERLIN ALEJANDRA', ['007617722', 'HERM-PEREIRA ROMERO', 'ANGELA ALEXANDRA', 'F'], ['008005853', 'ROMERO MARYORI', 'JOSEFINA', 'F']],
                ['62243886', 'POMAZONGO NAUPARI', 'ROSARIO ESPERANZA', ['04346618', 'POMAZONGO RICALDI', 'RAUL SALOME', 'M'], ['04346004', 'NAUPARI GONZALES', 'ROSA LUZ', 'F']],
                ['62470587', 'RAMIREZ PENA', 'YARETH DANA', ['40214377', 'RAMIREZ TRUJILLO', 'JIMMY ROGGER', 'M'], ['41205409', 'PENA MENDIZABAL DE RAMIREZ', 'RAQUEL', 'F']],
                ['62372119', 'REYES ULLOA', 'ANGHELINA DAMARIS', ['45113897', 'REYES ALARCON', 'DAVID', 'M'], ['42556717', 'ULLOA NATERES', 'JUDITH GIOVANNA', 'F']],
                ['62315928', 'THEO JURADO', 'YAREK LAMBERT', ['04307475', 'THEO ESPINOZA', 'DODGERS', 'M'], ['04342294', 'JURADO FLORES', 'JESSICA MAGDA', 'F']],
                ['62634310', 'VARGAS IZQUIERDO', 'LUCIANA MOHENA', ['40635961', 'VARGAS QUIJADA', 'FREDDY', 'M'], ['43195642', 'IZQUIERDO ESTRELLA', 'KATHERIN', 'F']],
                ['62171228', 'VELASCO HASSINGER', 'CRISLY FERNANDA', ['47329419', 'VELASCO PAREDES', 'DIETER ALBERT', 'M'], null],
                ['62214364', 'HUAYTA ARANA', 'JOSE PEDRO', ['42033957', 'HUAYTA BALTAZAR', 'ABEL PEDRO', 'M'], ['04341142', 'ARANA MENDOZA', 'MARIA FRANCISCA', 'F']],
                ['74511646', 'ALTAMIRANO DCONNOR', 'LADY LUCIANA', null, ['04086408', 'OCONNOR OLAZO', 'LADY KARINA', 'F']],
                ['60097126', 'GUERRA VILLANUEVA', 'JOHANA GREYS', ['04061973', 'ABUELO-ARREDONDO ALVARADO', 'ALEX', 'M'], ['04074282', 'RICALDE PANDURO', 'JUANA PAULINA', 'F']],
                ['62703545', 'YAURI MAMANI', 'DANIEL RODRIGO', null, ['41489081', 'MAMANI COAQUIRA', 'SONIA ERIKA', 'F']],
                ['81128648', 'PUENTE CRISOSTOMO', 'LUISA TARCILA', ['04349007', 'PUENTE YURVILCA', 'JACINTO', 'M'], ['04349006', 'CRISOSTOMO SIMON', 'RAYMUNDA ROSALIA', 'F']],
                ['62952205', 'ROSALES TOLENTINO', 'ANDREA ISABEL', ['44040518', 'ROSALES VALER', 'KENNETH DENNY', 'M'], ['43201703', 'TOLENTINO MALMAYAN', 'INA ROSARIO', 'F']],
                ['61646467', 'LOPEZ IGNACIO', 'JIMENA SHARIT CIELO', ['45558722', 'TIO-WERLEN ARCE', 'JOEL DAVID', 'M'], ['71202159', 'TIA-BORDA CRISPIN', 'ANDREA MILUSKA', 'F']],
                ['74261209', 'NOLBERTO HIDALGO', 'GENESIS YAMILE', null, ['44412724', 'HIDALGO ORTIZ', 'MEDALITH CRISTINA', 'F']],
            ],
        ],
        [
            'grado_orden' => 5,
            'estudiantes' => [
                ['61949070', 'ALEGRIA MATA', 'HAYKELL NEVENKA', ['4340781', 'ALEGRIA ESPINOZA', 'ITALO SANDRO', 'M'], ['41995658', 'MATA RUFFNER', 'MARGARITA PILAR', 'F']],
                ['61798039', 'BENDEZU REMENTERIA', 'MARIAJOSE CELESTE', ['40083972', 'BENDEZU SARMIENTO', 'SAMUEL EMERSON', 'M'], ['44280020', 'REMENTERIA SAMANIEGO', 'ELEN', 'F']],
                ['61910293', 'DELGADO PAUCAR', 'GISLI ALEXANDRA', ['40239618', 'DELGADO VICUNA', 'ELMER JAVIER', 'M'], ['40988483', 'PAUCAR GONZALES', 'NELLY', 'F']],
                ['62078973', 'HUAMAN HUAMAN', 'OWEN PARIS VANDERSAR', [null, 'HUAMAN ACOSTA', 'WILSON', 'M'], ['45892023', 'HUAMAN ALANIA', 'LUZMILA ZORAIDA', 'F']],
                ['61235579', 'JAMANCA ORDONEZ', 'ANDREA FERNANDA', ['04303701', 'JAMANCA HUAYWAS', 'EDWIN TODEO', 'M'], null],
                ['61647168', 'OROZCO CASTRO', 'SHIRLEY LETICIA', null, ['44427968', 'CASTRO CARHUACHUCO', 'VERONICA SUSAN', 'F']],
                ['61866346', 'PAREDES TORRES', 'HJOJAN BRAYAN', ['42668713', 'PAREDES APAZA', 'WILMER', 'M'], ['46217942', 'TORRES SALBATIERRA', 'OLGA LUZMILA', 'F']],
                ['61567934', 'QUINTANA ESPINOZA', 'JUAN MANUEL', ['71116419', 'HERM-QUINTANA ESPINOZA', 'SERGIO ALBERTO', 'M'], ['43180431', 'ESPINOZA GARAY', 'IRENE DELIA', 'F']],
                ['61656426', 'ROJAS JIMENEZ', 'MARJORIE FIORELLA', ['41117786', 'ROJAS ORIZANO', 'ENRIQUE', 'M'], ['09501163', 'JIMENEZ VARGAS', 'TEOFILA', 'F']],
                ['61949090', 'SOSA TEMBLADERA', 'YASOO ALEJANDRO', ['41117784', 'HUAMAN LOYA', 'EDWARD TIMOTEO', 'M'], ['46915882', 'TEMBLADERA ASTUPINAN', 'ZESY LAURA', 'F']],
                ['61741596', 'ZURITA BAUTISTA', 'LINDSAY ALISSON', ['72886971', 'TIO-BAUTISTA OLARTE', 'JOSE MANUEL', 'M'], ['42357598', 'BAUTISTA OLARTE', 'CARMEN ROSA', 'F']],
                ['61739654', 'OSPINA MACETAS', 'CHALLENGER', ['43171537', 'OSPINA MAYTA', 'IVAN RAUL', 'M'], ['46719011', 'MACETAS HINOJOSA', 'ADA INES', 'F']],
                ['60584374', 'ABARCA CURI', 'YEYSON MIGUEL', null, ['31932790', 'CRISTOBAL DE REA', 'DELFINA', 'F']],
                ['61739655', 'ESPINOZA ARANDA', 'YULIANA KAROL', ['04321179', 'ESPINOZA ZELADA', 'JESUS JORGE', 'M'], ['04346411', 'ARANDA DE LA CRUZ', 'SONIA DELINA', 'F']],
                ['62315947', 'LUNA HUAMAN', 'FRANCO FERNANDO', ['80216188', 'LUNA HUARANGA', 'LUIS ALBERTO', 'M'], ['43437983', 'HUAMAN AQUINO', 'ELSA', 'F']],
                ['61967824', 'CHAMORRO ALCANTARA', 'CLEIVER', ['45262490', 'CHAMORRO VILLAFUERTE', 'MAX PABLO', 'M'], ['60872230', 'SIN DATO', 'JENIFER', 'F']],
                ['61771475', 'ZAPATA BESARES', 'YARELY ESTEFANNI', ['41415569', 'ZAPATA ESTACA', 'ESTEBAN BONIFIOS', 'M'], ['46711946', 'BASARES RODRIGUEZ', 'CENAIDA', 'F']],
            ],
        ],
    ];

    public function run(): void
    {
        $anio = AnioEscolar::where('activo', true)->first()
            ?? AnioEscolar::orderByDesc('fecha_inicio')->first();

        if (! $anio) {
            $this->command?->warn('No hay anio escolar activo para registrar Secundaria.');
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
            $grado = Grado::where('nivel', 'Secundaria')
                ->where('orden', $grupo['grado_orden'])
                ->first();

            if (! $grado) {
                $this->command?->warn("No se encontro el grado {$grupo['grado_orden']} de Secundaria.");
                continue;
            }

            $seccion = Seccion::firstOrCreate(
                [
                    'anio_escolar_id' => $anio->id,
                    'grado_id' => $grado->id,
                    'nombre' => self::SECCION,
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

        $this->command?->info('Secundaria registrada con estudiantes y apoderados.');
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
        [$dniRaw, $apellidos, $nombres, $padreRaw, $madreRaw] = array_pad($estudianteData, 5, null);

        $dni = $this->normalizarDni($dniRaw);
        if (! $dni) {
            return;
        }

        $apellidos = trim((string) $apellidos);
        $nombres = trim((string) $nombres);
        $nombreCompleto = trim($apellidos . ' ' . $nombres);

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
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'estado' => 'activo',
            ]
        );

        if ((int) ($estudiante->user_id ?? 0) !== $user->id) {
            $estudiante->user_id = $user->id;
            $estudiante->save();
        }

        $padre = $this->registrarApoderado($padreRaw);
        $madre = $this->registrarApoderado($madreRaw);

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
                'estado' => Matricula::ESTADO_ACTIVA,
                'repitente' => false,
            ]
        );
    }

    private function registrarApoderado(?array $apoderadoData): ?Apoderado
    {
        if (! $apoderadoData) {
            return null;
        }

        [$dniRaw, $apellidos, $nombres, $sexo] = array_pad($apoderadoData, 4, null);

        $dni = $this->normalizarDni($dniRaw);
        $nombres = trim((string) $nombres);
        $apellidos = trim((string) $apellidos);

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
                'sexo' => $sexo,
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
