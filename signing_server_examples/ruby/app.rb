require 'base64'
require 'json'
require 'openssl'
require 'securerandom'
require 'sinatra'
require 'sinatra/cross_origin'

enable :cross_origin
set :port, 1337

APP_SECRET = 'THE.APP.SECRET.SHARED.BETWEEN.YOU.AND.OPENEDIO'
CLIENT_ID = 'YOUR.APP.ID.GRANTED.BY.OPENEDIO'

def base64_url_encode(str)
  Base64.encode64(str).tr('+/', '-_').gsub(/\s/, '').gsub(/=+\z/, '')
end

options '*' do
  response.headers['Allow'] = 'HEAD,GET,PUT,POST,DELETE,OPTIONS'
  response.headers['Access-Control-Allow-Headers'] = 'X-Requested-With, X-HTTP-Method-Override, Content-Type, Cache-Control, Accept'
  200
end

get '/' do
  send_file File.join('public/index.html')
end

get '/opened-api.js' do
  send_file File.join('../../opened-api.js')
end

post '/generate_signed_request' do
  envelope = @params
  envelope["client_id"] ||= CLIENT_ID
  envelope["algorithm"] ||= 'HMAC-SHA256'
  envelope["token"] ||= SecureRandom.hex #It's important that this is unique by user

  envelope = JSON.dump(envelope)
  encoded_envelope = base64_url_encode(envelope)

  signature = OpenSSL::HMAC.hexdigest(OpenSSL::Digest::SHA256.new, APP_SECRET, encoded_envelope)
  encoded_signature = base64_url_encode(signature)

  @signed_request = "#{encoded_signature}.#{encoded_envelope}"
  @client_id = CLIENT_ID

  erb :login_and_query
end
