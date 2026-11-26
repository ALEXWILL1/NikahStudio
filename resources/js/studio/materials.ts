export const backgrounds:Record<string,string> = {
    'floral-ivory':'Floral Ivory',
    'pusaka-toba':'Pusaka Toba',
    'hortensia-biru':'Hortensia Biru',
    'kebun-senja':'Kebun Senja',
    'zaitun-toscana':'Zaitun Toscana',
    'anggrek-amerta':'Anggrek Amerta',
    'gerbang-bali':'Gerbang Bali',
    'rembulan-malam':'Rembulan Malam',
    'peoni-merah':'Peoni Merah',
    'art-deco-emas':'Art Deco Emas',
    'taman-tropis':'Taman Tropis',
    'wisteria-pagi':'Wisteria Pagi',
    mawar:'Mawar mutiara', rimba:'Hutan tropis', toba:'Tepi Danau Toba', beludru:'Beludru merah anggur',
    hortensia:'Hortensia biru', senja:'Gerbang senja', zaitun:'Kertas zaitun', lilin:'Cahaya lilin', cincin:'Cincin champagne',
};
export const illustrations:Record<string,string> = {
    ivory:'Rangkaian mawar', olive:'Pohon zaitun', scroll:'Ukiran emas', hydrangea:'Buket hortensia',
    house:'Rumah Batak', palm:'Pohon palem', peony:'Peoni merah', wreath:'Lingkar daun',
    rings:'Sepasang cincin', jasmine:'Sulur melati', orchid:'Dahan anggrek', arch:'Bingkai emas',
};
export const backgroundModes:Record<string,string> = {
    print:'Ilustrasi penuh', full:'Foto penuh', arch:'Lengkung atas', band:'Pita foto', oval:'Medali oval', faded:'Foto lembut',
};
export function material(folder:'latar'|'ornamen', key:string) {
    return '/nikahstudio/'+folder+'/'+key+'.webp';
}
