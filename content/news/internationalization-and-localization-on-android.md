---
author: "Henry Addo"
date: 2016-06-01T11:08:17+09:00
draft: true
title: Best Practices For Supporting Internationalization and Localization On Android
twitter: "eyedol"
googlenewskeywords: ["localization", "translation", "android", "locale", "accessibility"]
Tags: ["Localization", "Translation", "Accessibility"]
---

At addhen, localization is something we take very seriously when developing an Android application. Though most of our apps aren't translated into most languages, we make sure we build apps that are localizable. That way in the future when we're ready to open the app to more regions and languages, we already have a strong foundation. I'm going to share some of our best practices for localization support.  If you're not familiar with what *internationalization* and *localization* are, I'll go ahead to define them.

**Internationalization** is the process of designing an application to make it possible for it to be localized to a particular region and language.

**Localization** is the act of making a product suited for a particular region and language that conforms to the region's *locale*.

**Locale** is a set of parameters that defines the user's language and region.

1. **Never hardcode user facing strings**

    At all cost we avoid hardcoding all static strings the user would have to see. We make sure we have them in their respective string resource file. For example,

    `mLableTextView.setText("Quantity");` This makes it hard to replace in the future when we want to translate `Quantity` into more languages. Instead we put that in a string resource and reference the name when setting the TextView's text. In `res/values/string.xml` we'll have 

    `<string name="quantity">Quantity</string>`

    Then reference it in code as `mLableTextView.setText(R.string.quantity);` using the string name. This way, when we want to translate `Quantity` into French we'll have to put it in `res/values-fr/string.xml` where `-fr` is the resource qualifer for the French language.

2. **Load fonts as localized string**

    When we make use of custom fonts, we don't hardcode that either in the view. This makes it possible for us to easily support fonts for a particular language. In the same way as we define string, we put the font name in a string resource file.
    
    `<string name="custom_font_name">fonts/custom_font.ttf</string>`
    
    Then declare a styleable attribute that can be referenced in the View's implementation. This also makes it possible to set the font in a layout resource file.
    
    {{< syntax-highlight xml >}}
    <!-- Inside res/values/attrs.xml -->
    <declare-styleable name="CustomView">
        <attr name="fontName" format="String"/>
    </declare-styleable>
    {{< /syntax-highlight >}}
    
    In our custom view implementation, we load and cache the font in memory. See sample code below.
    
    {{< syntax-highlight java >}}
    public CustomView(Context context, AttributeSet attributeSet) {
        super(context, attributeSet);
        initCustomFont(context, attributeSet);
    }

    private void initCustomFont(Context context, AttributeSet attributeSet) {
        final TypedArray attrs = context.obtainStyledAttributes(attributeSet, R.styleable.CustomView);
        final String fontName = attrs.getString(R.string.CustomView_fontName);
        final TypefaceManager typefaceManager = new TypefaceManager();
        final Typeface typeface = typefaceManager.getTypeface(context,fontName);
        if (typeface != null) {
            super.setTypeface(typeface);
        }
    }
    
    // Manages loading and caching of the font
    private static class TypefaceManager {

        private final LruCache<String, Typeface> mCache;

        public TypefaceManager() {
            mCache = new LruCache<>(3);
        }

        public Typeface getTypeface(final Contenxt context, final String filename) {
            Typeface typeface = mCache.get(filename);
            if (typeface == null) {
                typeface = Typeface.createFromAsset(context.getAssets(), filename);
                mCache.put(filename, typeface);
            }
            return typeface;
        }
    }
    {{< /syntax-highlight >}}

    In our xml `res/layout/label_item.xml` we then set the font as shown below.

    {{< syntax-highlight xml >}}
    <com.package.name.widget.CustomView
        ...
        app:fontName="@string/custom_font_name"/>
    {{< /syntax-highlight >}}

3. **Right-To-Left (RTL) support enabled**

    We enable `RTL` since it doesn't really require much work. It makes our layouts ready for switching to an `RTL` language. In 
    the application manifest, we enable layout mirroring by setting `android:supportsRtl` to `true`.
    {{< syntax-highlight xml >}}
    <application
        ...
        android:supportsRtl="true"/>
    {{< /syntax-highlight >}}

    If we're targing `API 17` and above, we replace all `left/right` layout properties to the new `start/end` equivalents. So `paddingLeft` becomes `paddingStart` and `paddingRight` becomes `paddingEnd`. Same for margin directional properties. It requires a bit of work when targeting below `API 17`. Instead of supporting different layouts which makes maintenance a headache, we put these properties in a style resource for the different API versions. See sample code below.
    {{< syntax-highlight xml >}}
    <!-- res/values-v17/styles.xml targets API level 17+ -->
    <style name="TextView.Label">
        ...
        <item name="android:layout_marginStart">16dp</item>
        <item name="android:layout_marginEnd">16dp</item>
        ...
    </style>

    <!-- res/values/styles.xml targets API 17- -->
    <style name="TextView.Label">
        ...
        <item name="android:layout_marginLeft">16dp</item>
        <item name="android:layout_marginRight">16dp</item>
        ...
    </style>

    <!-- res/layout/screen_layout.xml -->
    <com.package.name.widget.CustomView
        style="@style/TextView.Label"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        app:fontName="@string/custom_font_name"/>
    {{< /syntax-highlight >}}

4. **Localize line heights dimensions**

    At all cost we don't hardcode value for the `android:lineSpacingMultiplier` property. This way, it's easier to localize line heights value. Some langagues will need bigger line spacing than others.

    {{< syntax-highlight xml >}}
    <!-- res/values/integers.xml -->
    <resources>
        <item name="line_height" format="float" type="integer">0.5</item>
    </resources>

    <!-- res/values-fr/integers.xml -->
    <style name="TextView.Label">
        <item name="line_height" format="float" type="integer">1.0</item>
    </style>

    <!-- res/layout/screen_layout.xml -->
    <com.package.name.widget.CustomView
        style="@style/TextView.Label"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:lineSpacingMultiplier="@integer/line_height"
        app:fontName="@string/custom_font_name"/>
    {{< /syntax-highlight >}}

5. **Test using pseudolocalization**

    Since most of our apps aren't translated into different languages, we take advantage of pseudo-localization provided by Android. Pseudo-localization in general is a method of testing the internationalization of text while maintaining its readability. We do this more to expose issues regarding length and flow of text and layout issues. In our debug builds, we enable pseudolocalization by setting `pseudoLocalesEnabled` to `true`

    {{< syntax-highlight java >}}
    // In build.gradle file
    buildTypes {
        debug {
            pseudoLocalesEnabled true
        }
    }
    {{< /syntax-highlight >}}

    Then configure a device to make use of the `en_XA` locale. This gives us texts which are longer and with accents. And to test the layout mirroring (RTL), we configure the device to another locale `ar_XB`. This shifts everything from `Left-Right` to `Right-Left`.

    On a compatible Android device go to settings and select **Language & Input** > **Language** > **English (XA)** to enable the `en_XA` locale.

    Go to settings and select **Language & Input** > **Language** > **(XB) العربية** to enable the `ar_XB` locale.

As good practice, we make sure before we make a final release to the Playstore, we've tested our apps in at least 3 or more different languages to visually inspect how the app will behave in these langagues. Though this gives us extra workload it makes us confident that, our app doesn't only function well but it's appears good in other languages.

The android documentation has a [checklist](https://developer.android.com/distribute/tools/localization-checklist.html) for localization that you can go through to make your app localizable which I recommend reading.