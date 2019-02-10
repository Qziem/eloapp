open EloTypes;

[%bs.raw {|require('./Layout.scss')|}];

type action =
  | SetPlace(place);

let component = ReasonReact.reducerComponent("Layout");

let getPlaceFromUrl = (url: ReasonReact.Router.url) =>
  switch (url.hash) {
  | "rank_and_stats" => RANK_AND_STATS
  | "operations" => OPERATIONS
  | _ => NOT_FOUND
  };

let getSubtitle = place =>
  switch (place) {
  | RANK_AND_STATS => "Ranking"
  | OPERATIONS => "Operations"
  | NOT_FOUND => "Not found"
  };

let initialState = () =>
  ReasonReact.Router.dangerouslyGetInitialUrl() |> getPlaceFromUrl;

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
      let place = getPlaceFromUrl(url);
      send(SetPlace(place));
    })
    |> ignore,
  render: ({state}) =>
    <div className="layout">
      <DocumentTitle title=state->getSubtitle->Helpers.createTitle />
      <Menu activePlace=state />
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