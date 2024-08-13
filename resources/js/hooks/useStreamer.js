import streamers from "@/streamers";

export default function useStreamer() {
  //   const route = route().current();
  const route = "vine";

  const streamer = streamers.find((streamer) =>
    streamer?.supportedRoutes?.includes(route)
  );

  return streamer;
}
