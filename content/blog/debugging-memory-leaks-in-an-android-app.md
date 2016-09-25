---
author: "Henry Addo"
date: 2016-09-25T11:34:07+09:00
draft: true
googlenewskeywords: ["debugging", "memory leaks"]
images: []
tags: ["Debugging", "Memory leaks"]
title: Debugging Memory Leaks In An Android App
twitter: "eyedol"
vidoes: []
---

Debugging **memory leaks** in an android app can be a bit tricky. Recently we had to deal with this issue in one of our apps. This was caused due to a recent refactoring we did in our code base. We were holding on to an activity instance that needed to be Garbage Collected**(GC'd)** after the activity was destroyed but was still linkering around. Yes, you would think the Android Runtime**(ART)** will GC this instance but no. There was another living object in the application that was still holding on to the activity instance so it couldn't be GC'd.

Finding out all these information just by reading the codebase can be difficult and time consuming. Luckily there are monitoring tools out there that helps in reporting and investigating **memory leaks**. Android Studio comes with a [Memory Monitor][2] that essentially monitors memory activities by your app. You can then dump the Java Heap and analyize it for optimizations and possible memory leaks. There is also [Leak Cannary][2] by the folks at Square. It's allows you to integrate memory monitoring directly in your app so it reports any memory leaks in your app.

I'm going to share with you how we found out that our app was leaking memory, the problematic code and the refactoring we did to eliminate the issue.

### Investigating Memory Leaks
In our app's devel flavor, we have leak cannary enabled. This is a practise we do with all of our apps as it makes it easier to detect and fix memory leaks as we develop. With our devel build variant launched in an emulator, we used it like a regular user will use the app. We fetched the needed content, scrolled through them and nothing really happened. The issue showed up just when we rotated the emulator to change screen orientation, after few seconds, Leak Cannary reported a memory leak, followed by a dump of our app's heap. Leak cannary displays the heap in an optimized way so it's easier to find which dominant object still referencing an object that needs to be GC'd. 

**TODO:** 

1. Add screenshots for leak cannary.
2. Add screenshots for android memory monitor.
3. Add screenshots for memory analyzer.

### The Defected Code

This leaks an Activity.

{{< syntax-highlight java >}}
public class Launcher {
	
	 // This will be strongly held by the instance of the Launcher class.
   	 private Activity mActivity;

    @Inject
    public Launcher(Activity activity) {
        mActivity = activity;
    }

    public void launchReviewList(Context context, Long movieId) {
        context.startActivity(ListReviewsActivity.getIntent(context, movieId));
    }
	// This is bad code
    public void launchMovieDetails(FragmentActivity activity, Long movieId, View view) {
        ActivityOptionsCompat options = ActivityOptionsCompat.makeSceneTransitionAnimation(
                // The context of the activity
                activity,
                Pair.create(view, activity.getString(R.string.transition_movie_details)),
                Pair.create(view, activity.getString(R.string.transition_movie_details_background))
        );
        ActivityCompat.startActivity(activity, DetailMovieActivity.getIntent(mActivity, movieId),
                options.toBundle());
    }

    public void launchReminders(Context context) {
        context.startActivity(ListRemindersActivity.getIntent(context));
    }
}

{{< /syntax-highlight >}}

The dominant object, `mLauncher` holding on to the instance of the activity class.

{{< syntax-highlight java>}}
public class ListMovieFragment extends BaseRecyclerViewFragment<MovieModel, MovieAdapter> implements
        MovieListView, RecyclerViewItemTouchListenerAdapter.RecyclerViewOnItemClickListener,
        OnLoadMoreListener, SwipeRefreshLayout.OnRefreshListener {

    @BindView(R.id.loading_list)
    ProgressBar mProgressBar;

    @BindView(android.R.id.empty)
    View mEmptyView;

    @Inject
    ListMoviePresenter mListMoviePresenter;

    @Inject
    Launcher mLauncher;

    private boolean mIsPaginating;

    private boolean isFromInternet;

    public ListMovieFragment() {
        super(MovieAdapter.class, R.layout.fragment_movie_list, 0);
        //setRetainInstance(true);
    }
}

{{</syntax-highlight>}}

An instance of `Launcher` class, `mLauncher` is declared in the `ListMovieFragment` class. This fragment class still exist after the main activity hosting it is destroyed. It's referencing an instance of the MainActivity which is declared in the `Launcher` class as `mActivity`

### The Refactored Code
{{< syntax-highlight java>}}
public class Launcher {

    @Inject
    public Launcher() {
    }

    public void launchReviewList(Context context, Long movieId) {
        context.startActivity(ListReviewsActivity.getIntent(context, movieId));
    }

    public void launchMovieDetails(Activity activity, Long movieId, View view) {
        ActivityOptionsCompat options = ActivityOptionsCompat.makeSceneTransitionAnimation(
                // The context of the activity
                activity,
                Pair.create(view, activity.getString(R.string.transition_movie_details)),
                Pair.create(view, activity.getString(R.string.transition_movie_details_background))
        );
        ActivityCompat.startActivity(activity, DetailMovieActivity.getIntent(activity, movieId),
                options.toBundle());
    }

    public void launchReminders(Context context) {
        context.startActivity(ListRemindersActivity.getIntent(context));
    }
}

{{</syntax-highlight>}}

We removed the global instance of Activity, `mActivity` and instead passed it as a parameter to the method that needs it. This way it can be `GC'd` easily as there is no strong hold of it anymore.

If you're curious about memory leaks in general and ways to avoid them, you can read these great articles belows.

1. [Hunting Memory Leaks][3]
2. [Eight Ways Your Android App Can STOP Leaking memory][4]
3. [Memory Monitor][5]
4. [HPROF Viewer and Analyzer][6]
5. [Investigating Your RAM Usage][7]

[1]: https://developer.android.com/studio/profile/am-memory.html
[2]: http://squareup.com
[3]: https://www.toptal.com/java/hunting-memory-leaks-in-java 
[4]: http://blog.nimbledroid.com/2016/09/06/stop-memory-leaks.html
[5]: https://developer.android.com/studio/profile/am-memory.html
[6]: https://developer.android.com/studio/profile/am-hprof.html#hprof-diving
[7]: https://developer.android.com/studio/profile/investigate-ram.html