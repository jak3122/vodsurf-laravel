import streamers from "@/streamers";

export default function useStreamer() {
  //   const route = route().current();
  const route = "jerma";

  const streamer = streamers.find((streamer) =>
    streamer?.supportedRoutes?.includes(route)
  );

  return streamer;
}
