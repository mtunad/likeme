Teknik Seçimler ve Geliştirme Notları
===
Okul yoğunluğu, referandum vs gibi sebeplerden dolayı geliştirmeye geç başlayabildim. Bu sebeple ortaya
çıkan üründen memnun değilim, ancak verilen görev güzel bir görevdi, geliştirmeye devam edip
Wordpress marketine ilerleyen haftalarda göndereceğim.
 
Kullanıcıya eklentiye doğrudan erişmesi, yetkisi olmayan kullanıcıların etiketlerin beğenilmesini
görememesi, ajax isteklerini elle göndererek spamplenebilmesi, IP bazlı oylama kısıtlama
gibi konularda güvenlik önlemleri alınmadı.

Eklentiyi kaldırırken, eklenmiş meta verilerin kaldırılmaması dolayısıyla veritabanında gereksiz bir
yük olmuş olacak. Bununla ilgili kısmı istenmediğinden yapmadım.

Safari'deki gizli gezinti sırasında `localStorage` erişilemediğinden, eklentiyi gizli gezinti sırasında
kullanan birisi oy kullanamayacak.

Widget geliştirmesi sırasında kullanıcıya daha fazla şey sorulabilirdi (kaç tane post gösterilmeli,
yazıların yanında upvote miktarları gösterilmeli mi gibi) ancak istenmediğinden yapmadım.

Eklentinin yerleştirmiş olduğum butonuna kolay bir şekilde stil değişikliği yapılamıyor. Bu ciddi
bir dezavantaj, çünkü çoğu temada güzel durmayacaktır, eğreti duracaktır. Dolayısıyla esnek bir biçimde
 biçimlendirilebilmesi önemli. Fakat istenmediğinden yapmadım.

Etiketlerin beğenilmesini gösteren sayfada, sayfalama işlemi için `Wordpress API`'deki `pagination` kullanmamış oldum.
Daha kestirme, ancak daha performanslı bir yöntem olduğunu düşündüğüm kullanıcı tarafında Javascript
ile tabloyu sayfalandırdım ve sıralanmasına olanak sağladım. Bu hedeflenen göreve göre 
biraz kaçak bir yöntem olmuş oldu elbette.

Zamanım kalmadığından bir benchmark testi yapamadım, dolayısıyla eklentideki sorguların verimliliği
üzerine net bir şey söyleyemiyorum. Fakat etiketlerin beğenilme sayıları sayfasında tüm etiketlerin
üzerine gidip, oradaki tüm postalardan bir looplar döngüsü içinde toplam beğenme sayısı bulmak pek
hoş bir yöntem değil gibi. İyileştireceğim.