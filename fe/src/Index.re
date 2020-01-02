[%bs.raw {|require('./Index.scss')|}];
ReactDOMRe.renderToElementWithId(
  <ReasonApollo.Provider client=ApiClient.instance>
    <AppContainer />
  </ReasonApollo.Provider>,
  "mountPoint",
);