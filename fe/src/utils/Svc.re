open Js.Promise;

let svcGeneric = (method, resource, payload) =>
  Fetch.fetchWithInit(
    "/api/" ++ resource,
    Fetch.RequestInit.make(
      ~method_=method,
      ~body=Fetch.BodyInit.make(Js.Json.stringify(payload)),
      ~headers=Fetch.HeadersInit.make({"Content-Type": "application/json"}),
      (),
    ),
  )
  |> then_(Fetch.Response.json);

let svcGet = resource =>
  Fetch.fetch("/api/" ++ resource) |> then_(Fetch.Response.json);

let svcPost = svcGeneric(Post);
let svcPut = svcGeneric(Put);