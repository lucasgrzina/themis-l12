import axios from 'axios'
import config from '../config'

export default {
  request (method, uri, data = null) {
    if (!method) {
      console.error('API function call requires method argument')
      return
    }

    if (!uri) {
      console.error('API function call requires uri argument')
      return
    }

    var _uri = (uri[uri.length-1] === '/' ? uri.substring(0,uri.length-1) : uri);
    var url = config.serverURI + _uri

    return axios({ method, url, data })  
  },

  store (uri,data) {
    var method = data.id > 0 ? 'PUT' : 'POST';
    var _uri = data.id > 0 ? uri.concat(data.id) : (uri[uri.length-1] === '/' ? uri.substring(0,uri.length-1) : uri);
    return this.request(method,_uri,data);
  },

  post (uri,data) {
    return this.request('POST',uri,data)
      .then((result) => {
        return result.data;
      });
  },

  delete (uri,data) {
    var method = 'DELETE';
    var uri = data && data.id && data.id > 0 ? uri.concat(data.id) : uri;

    return this.request(method,uri,data);

  },
  get (uri) {
    return this.request('GET', uri)
      .then((result) => {
        return result.data;
      });
  },

  combos (combo) {
    return this.request('GET', 'combos/' + combo)
      .then((result) => {
        return result.data;
      });
  }

}
