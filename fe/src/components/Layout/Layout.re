type placeState =
  | RANK_AND_STATS
  | OPERATIONS
  | NOT_FOUND;

type action =
  | SetPlace(placeState);

let component = ReasonReact.reducerComponent("Layout");

let initialState = () => RANK_AND_STATS;

let reducer = (action, _state) =>
  switch (action) {
  | SetPlace(place) => ReasonReact.Update(place)
  };

let make = _children => {
  ...component,
  initialState,
  reducer,
  didMount: ({send}) =>
    ReasonReact.Router.watchUrl(url => {
      Js.log("hash: " ++ url.hash);
      switch (url.hash) {
      | "rank_and_stats" => send(SetPlace(RANK_AND_STATS))
      | "operations" => send(SetPlace(OPERATIONS))
      | _ => send(SetPlace(NOT_FOUND))
      };
    })
    |> ignore,
  render: ({state}) =>
    <div className="layout">
      <Menu />
      <div className="eloContent">
        {
          switch (state) {
          | RANK_AND_STATS => <RankAndStats />
          | OPERATIONS => <Operations />
          | NOT_FOUND => "Not found" |> ReasonReact.string
          }
        }
      </div>
    </div>,
};