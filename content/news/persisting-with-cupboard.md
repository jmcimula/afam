---
author: "Henry Addo"
date: 2016-06-27T09:50:18+09:00
draft: true
title: Persisting Data Objects With Cupboard
twitter: "eyedol"
googlenewskeywords: ["cupboard", "json", "objects", "android", "design", "gson"]
Tags: ["Storing", "Database"]
---

There are serveral contenders when it comes to persisting data on Android.
At addhen, our favourite is [cupboard][1]. It's a lightweight data storage for Android. At first, it seems like an [ORM][2] but it isn't, as it doesn't manage table relationships and all that fancy features that comes with it. It gives you a neat API to store and retrieve data objects on Android.

We structure our database tables around the `JSON` response we get from API calls. At times we're tempted to create tables just to conform to the structure of the `JSON` string. Hence sometimes we wished `cupboard` could manage table relationships. But it does give you the flexibility to do all of that. It just means you will have to write a bit of code to achieve that. 

In most cases when we look at the structure of our `JSON` string, we look for ways to elimenate the need to create extra tables. To achieve that, we try to store some part of the `JSON` string in it serialized format. Then deserialize it upon retrieval. Cupboard provides you with the necessary mechanism to acheive all that. We're going to share with you how we approach this.

So let's say we have this `JSON` string

{{< syntax-highlight java >}}[
  {
    "id": 1,
    "name": "Apples",
    "price": 10,
    "note": "I usually buy them from the convenient store.",
    "expiry_date": "28-07-2017",
    "category": {
      "id": 9,
      "url": "http://www.api.data.com/categories/9"
    },
    "quantity": {
      "initial": "10",
      "remaining": "5",
      "unit": "None",
      "reorder": 3
    }
  },
  {
    "id": 2,
    "name": "Arugula",
    "price": 200,
    "note": "I got them from the amazon jp store",
    "expiry_date": "16-07-2018",
    "category": {
      "id": 9,
      "url": "http://www.api.data.com/categories/9"
    },
    "quantity": {
      "initial": "6",
      "remaining": "2",
      "unit": "box",
      "reorder": 3
    }
  }
]
{{< /syntax-highlight >}}

The [POJO][3] for this will be

{{< syntax-highlight java >}}public class InventoryEntity extends Data {

    public String name;

    public float price;

    public String note;

    public CategoryEntity category;

    public Quantity quantity;

    public Date expiryDate;

    public InventoryEntity() {
        // Do nothing
    }

    public InventoryEntity(Long id, String name, float price, Quantity quantity,
            String note, CategoryEntity category, Date expiryDate) {
        this._id = id;
        this.name = name;
        this.price = price;
        this.quantity = quantity;
        this.note = note;
        this.category = category;
        this.expiryDate = (Date) expiryDate.clone();
    }

    public static class Quantity {

        public int initial;

        public int remaining;

        public int reorder;

        public String unit;

        public Quantity(int initial, int remaining, int reorder, String unit) {
            this.initial = initial;
            this.remaining = remaining;
            this.reorder = reorder;
            this.unit = unit;
        }
    }
}
{{< /syntax-highlight >}}

Since `cupboard` stores objects into the database, this `POJO` should just work right? But no, it doesn't. It wouldn't know how to handle the `Quantity` property as it's a subclass. In a regular database relationship, you could create a table to hold the `Quantity` class but you can eliminate that, if you could just store that part as a `JSON` string. This alleviates the pain of managing table relationships.

We utilize cupboard's converters to achieve that. In that when saving the object, we intercept the process and keep the `Quantity` property in this case, as a regular `JSON` string. We create a custom `FieldConverter` and use it to check if cupboard is about to process the `Quantity` field, then save the `JSON` string instead of the deserialized format.

First, create a generic field converter using `GSON`

{{< syntax-highlight java >}}public class GsonFieldConverter<T> implements FieldConverter<T> {

    private final Gson mGson;

    private final Type mType;

    public GsonFieldConverter(Gson gson, Type type) {
        mGson = gson;
        mType = type;
    }

    @Override
    public T fromCursorValue(Cursor cursor, int columnIndex) {
    	  // Convert from JSON string to POJO
        return mGson.fromJson(cursor.getString(columnIndex), mType);
    }

    @Override
    public void toContentValue(T value, String key, ContentValues values) {
    	 // Convert from POJO to JSON string
        values.put(key, mGson.toJson(value));
    }

    @Override
    public EntityConverter.ColumnType getColumnType() {
        return EntityConverter.ColumnType.TEXT;
    }
}
{{< /syntax-highlight >}}

Second, by some [Reflection][4] magic, cupboard allows you to figure out which field it's processing, allowing you to process that field before it stores or retrieves it from the database.

{{< syntax-highlight java >}}public class InventoryEntityConverterFactory extends ReflectiveEntityConverter<InventoryEntity> {

    /**
     * Default constructor
     *
     * @param cupboard The {@link Cupboard} object
     */
    public InventoryEntityConverterFactory(Cupboard cupboard) {
        super(cupboard, InventoryEntity.class);
    }

    @Override
    protected FieldConverter<?> getFieldConverter(Field field) {
        if ("quantity".equals(field.getName())) {
        	  // Use the field converter to deserilize/serialize the quantity field
            return new GsonFieldConverter<>(new Gson(),
                    new TypeToken<InventoryEntity.Quantity>() {
                    }.getType());
        }
        return super.getFieldConverter(field);
    }
}  
{{< /syntax-highlight >}}

Third, we have to initialize this when registering the entities with cupboard otherwise all this implementation won't make any sense to it.


{{< syntax-highlight java >}}private static final Class[] ENTITIES = new Class[]{
    InventoryEntity.class, CategoryEntity.class,
};

static {
    EntityConverterFactory factory = new EntityConverterFactory() {

        @Override
        public <T> EntityConverter<T> create(Cupboard cupboard, Class<T> type) {
            if (type == InventoryEntity.class) {
                return (EntityConverter<T>) new InventoryEntityConverterFactory(cupboard);
             }
             return null;
        }
    };

    CupboardFactory.setCupboard(new CupboardBuilder()
                .registerEntityConverterFactory(factory).useAnnotations().build());
    // Register our entities
    for (Class<?> clazz : ENTITIES) {
        cupboard().register(clazz);
    }
}
{{< /syntax-highlight >}}

To learn more about cupboard, check its [wiki][1]. It has lots of how-tos to get you started.

[1]: https://bitbucket.org/littlerobots/cupboard/wiki/Home
[2]: https://en.wikipedia.org/wiki/Object-relational_mapping
[3]: https://en.wikipedia.org/wiki/Plain_Old_Java_Object
[4]: https://docs.oracle.com/javase/tutorial/reflect/