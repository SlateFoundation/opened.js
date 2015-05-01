require 'base64'
require 'json'
require 'openssl'
require 'securerandom'
require 'sinatra'
require 'sinatra/cross_origin'

enable :cross_origin
set :port, 1337

APP_SECRET = '7e728afdf46092c3d9c83ae2ea712e80d6dcc13e4336af1343541231856bec64'
CLIENT_ID = 'd5469f8af59cb6f81eb63f5aa3debc3c509f0597690d3ced227d1bf9348fba7b'

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

get '/oauth.js' do
  send_file File.join('../../oauth.js')
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

  erb :test
end
