---
author: "Henry Addo"
date: 2016-06-20T11:04:47+09:00
draft: true
title: Android Weekly Recap June 20th
twitter: "eyedol"
---

Here are our picks of interesting links from last week.

1. {{< recap-heading-links href="http://android-developers.blogspot.jp/2016/06/android-n-apis-are-now-final.html" >}} Android N's APIs are now final, get your apps ready for Android N! {{< /recap-heading-links >}}

	The Android team have finally finalized the Android N APIs. The version number is offically 24 now. You can update your `compiledSdkVersion` to API 24 to develop with Android N APIs. You can download it via Android studio's SDK manager. {{< more href="http://android-developers.blogspot.jp/2016/06/android-n-apis-are-now-final.html" >}}

2. {{< recap-heading-links href="http://saulmm.github.io/the-powerful-android-studio" >}} The powerful Android Studio {{< /recap-heading-links >}}

	This post gives you a comprehensive guide on using Android studio without much use of the mouse. It gives you a list of keyboard shortcuts to help you navigate and use Android studio effectively. {{< more href="http://saulmm.github.io/the-powerful-android-studio" >}}

3. {{< recap-heading-links href="https://medium.com/@froger_mcs/inject-everything-viewholder-and-dagger-2-e1551a76a908#.f59m3tmbm" >}} Inject everything — ViewHolder and Dagger 2 (with Multibinding and AutoFactory example){{< /recap-heading-links >}}

	This is a part of series of articles showing Dependency Injection with Dagger 2 framework in Android and talks about the use of [Multibinding][1], [Autofactory][2] to implement the ViewHolder pattern. {{< more href="https://medium.com/@froger_mcs/inject-everything-viewholder-and-dagger-2-e1551a76a908#.f59m3tmbm" >}}

4. {{< recap-heading-links href="http://genius.engineering/blog/2016/6/9/a-smaller-sleeker-apk-using-the-package-analyzer" >}} A smaller, sleeker app using the APK Analyzer {{< /recap-heading-links >}}

	If you've ever had the issue with huge APK size, this articles talks about how the team at Genius used the new APK Analyzer to find which part of their code is causing them a big APK size. It goes on to give solutions on how they trimmed its size down. {{< more href="http://genius.engineering/blog/2016/6/9/a-smaller-sleeker-apk-using-the-package-analyzer" >}}

5. {{< recap-heading-links href="https://commonsware.com/blog/2016/06/16/random-musings-n-developer-preview-4.html" >}} Random Musings on the N Developer Preview 4 {{< /recap-heading-links >}}

	Mark Murphy, gives deeper insight into the changes in Android N Developer Preview 4. {{< more href="https://commonsware.com/blog/2016/06/16/random-musings-n-developer-preview-4.html" >}}

6. {{< recap-heading-links href="http://blog.nimbledroid.com/2016/06/15/app-diets-not-a-fad.html" >}} App Diets are not a Fad {{< /recap-heading-links >}}

	The team at Nimbledroid, share some strategies in reducing APK size leading to faster resource lookup and faster reflection. {{< more href="http://blog.nimbledroid.com/2016/06/15/app-diets-not-a-fad.html" >}}

7. {{< recap-heading-links href="https://android.googleblog.com/2016/06/introducing-nearby-new-way-to-discover.html">}} Introducing Nearby: A new way to discover the things around you {{< /recap-heading-links >}}

	The next updates to Google Play Services brings the nearby feature. This works on Android 4.4(KitKat) and above. The near by feature is a smart way to discover relevant apps and websites based on things around you. {{< more href="https://android.googleblog.com/2016/06/introducing-nearby-new-way-to-discover.html" >}}

[1]: http://google.github.io/dagger/multibindings.html
[2]: https://github.com/google/auto/tree/master/factory