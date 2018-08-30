open Js.Promise;

let svcGet = resource =>
  Fetch.fetch("be/services/svc.php/" ++ resource)
  |> then_(Fetch.Response.json);

let svcPost = (resource, payload) =>
  Fetch.fetchWithInit(
    "be/services/svc.php/" ++ resource,
    Fetch.RequestInit.make(
      ~method_=Post,
      ~body=Fetch.BodyInit.make(Js.Json.stringify(payload)),
      ~headers=Fetch.HeadersInit.make({"Content-Type": "application/json"}),
      (),
    ),
  )
  |> then_(Fetch.Response.json);