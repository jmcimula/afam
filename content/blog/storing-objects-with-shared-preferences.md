---
author: "Henry Addo"
date: 2016-05-16T14:02:21+09:00
draft: true
title: Storing Objects With Shared Preferences
twitter: "eyedol"
---
As you know [SharedPreferences][1] allows you to store key-value pairs of primitive data types in an XML and provides you with a decent API for storing/retrieving their values. It's mostly used to persist values from Preferences/Settings screens.

In a recent project, we wanted to make use of the **Shared Preferences** framework to store an object's properties more so for convenience. Usually we'll store these in an SQLite database but with this use case we thought it was an overkill to do that. Instead, we leveraged on **JSON** and the [SharedPreferences][1] class.

The design in it basic form: We serialize the object into a JSON string upon storing it and then store the regular string. Upon retrieving it, we deserialize the JSON string back into the object. Mind you this operation can be costly when the object being operated on has huge properties. So if you're planning on using this technique for large objects, it would be better to make use of the other storage options provided by the Android framework.

The design in it detailed form: We designed a simpile persistent storage with [GSON][2], the underlining serialization mechanisim and [Shared Preferences][3], the storage engine. We made it in such a way that you can use which ever storage or serialization engine you prefer.

We provided two interfaces, one for the Serialization mechanism and another for the Storage engine. To share some code, here is the interface for implementing the serialization mechanism.

{{< syntax-highlight java >}}public interface SerializationMechanism<T> {

    /**
     * Serializes a T to a JSON string
     *
     * @param entity The type entity to be serialized
     * @return String The serialized object into a JSON string
     */
    String serialize(T entity);

    /**
     * Deserializes a JSON string to it's typed entity
     *
     * @param serializedEntity The serialized object in a JSON format
     * @return A type entity
     */
    T deserialize(String serializedEntity);
}
{{< /syntax-highlight >}}

And the interface for the storage engine.

{{< syntax-highlight java >}}
public interface StorageMechanism<T> {

    /**
     * Gets an {@link rx.Observable} which will emit a list of {@link EntityType}.
     */
    Observable<List<EntityType>> get();

    /**
     * Gets an {@link rx.Observable} which will emit a {@link EntityType}.
     */
    Observable<EntityType> get(String key);

    /**
     * Puts an element into storage
     *
     * @param The unique key to identify this entity type
     * @param entityType Element to insert into storage.
     *
     * @return  The stored entity type
     */
    Observable<EntityType> put(String key, EntityType entityType);

    /**
     * Deletes a particular entity type
     */
    Observable<Boolean> delete(String key);

    /**
     * Delete all persisted elements
     */
    Observable<Boolean> deleteAll();
}
{{< /syntax-highlight >}}

Now let's look at their respective implementations.

The serialization mechanism:

{{< syntax-highlight java>}}public class GsonSerializationMechanism implements SerializationMechanism<EntityType> {

    private final Gson mGson = new Gson();

    private final Type mTypeToken = new TypeToken<List<EntityType>>() {}.getType();

    public String serialize(EntityType entityType) {
        return mGson.toJson(entityType, mTypeToken);
    }
    
    public EntityType deserialize(String jsonString) {
        return mGson.fromJson(jsonString, mTypeToken);
    }
}
{{< /syntax-highlight >}}

The storage mechanism implementation: This is an [RxJava][4] based implementation. You will noticed most of the functions are returning an Observable. You can do away with the RxJava implementation if you don't 
find that useful for your use case.

{{< syntax-highlight java >}}public class SharedPreferenceStorageMechanism implements StorageMechanism<EntityType> {

    private final SharedPreferences mSharedPreferences;

    private final SerializationMechanism mSerializationMechanism;

    public SharedPreferenceStorageMechanism(SharedPreferences sharedPreferences, 
        StorageMechanism serializationMechanism) {
        mSharedPreferences = sharedPreferences;
        mSerializationMechanism = serializationMechanism;
    }

    @Override
    public Observable<EntityType> put(@NonNull String key, @NonNull EntityType entityType) {
        return Observable.create(subscriber -> {
            mSharedPreferences.edit().putString(key,mSerializationStrategy.serialize(entityType)).apply();
            subscriber.onNext(entityType);
            subscriber.onCompleted();
        });
    }

    @Override
    public Observable<Boolean> delete(@NonNull String key) {
        return Observable.defer(() -> {
        if (TextUtils.isEmpty(key)) {
            return Observable.just(Boolean.FALSE);
        }
        mSharedPreferences.edit().remove(key).apply();
            return Observable.just(Boolean.TRUE);
       });
    }

    @Override
    public Observable<Boolean> deleteAll() {
        return Observable.defer(() -> {
            mSharedPreferences.edit().clear().apply();
            return Observable.just(Boolean.TRUE);
        });
    }

    @Override
    public Observable<List<EntityType>> get() {
        return Observable.create(subscriber -> {
            Map<String, String> savedTypes = (Map<String, String>) mSharedPreferences
                    .getAll();
            List<EntityType> entitiesTypes = new ArrayList<EntityType>();
            for (Map.Entry entry : savedTypes.entrySet()) {
                entitiesTypes.add(mSerializationStrategy.deserialize((String) entry.getValue()));
            }
            subscriber.onNext(entitiesTypes);
            subscriber.onCompleted();
        });
    }

    @Override
    public Observable<EntityType> get(String key) {
        return Observable.create(subscriber -> {
            EntityType entityType = getStored(key);
            if (entityType != null) {
                subscriber.onNext(entityType);
                subscriber.onCompleted();
            } else {
                subscriber.onError(new NotFoundException());
            }
        });
    }

    private EntityType getStored(String key) {
        final String jsonString = mSharedPreferences.getString(key, null);
        return mSerializationStrategy.deserialize(jsonString);
    }
}
{{< /syntax-highlight >}}

This so far has been working greatly in our current use case. Hope you will find this useful in your projects.

[1]: http://developer.android.com/reference/android/content/SharedPreferences.html
[2]: https://github.com/google/gson
[3]: http://developer.android.com/guide/topics/data/data-storage.html#pref
[4]: https://github.com/ReactiveX/RxJava

